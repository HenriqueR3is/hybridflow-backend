import { useEffect, useState } from "react";
import RoomForm from "./components/RoomForm";
import RoomList from "./components/RoomList";
import ReservationForm from "./components/ReservationForm";
import "../src/index.css";

function App() {
  const [rooms, setRooms] = useState([]);
  const [name, setName] = useState("");
  const [capacity, setCapacity] = useState("");
  const [editingRoom, setEditingRoom] = useState(null);
  const [roomId, setRoomId] = useState("");
  const [startTime, setStartTime] = useState("");
  const [endTime, setEndTime] = useState("");
  const [suggestions, setSuggestions] = useState([]);
  const [message, setMessage] = useState("");

  const fetchRooms = () => {
    fetch("http://127.0.0.1:8000/api/rooms")
      .then((res) => res.json())
      .then((data) => setRooms(data))
      .catch((err) => console.error(err));
  };

  useEffect(() => {
    fetchRooms();
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();

    const url = editingRoom
      ? `http://127.0.0.1:8000/api/rooms/${editingRoom.id}`
      : "http://127.0.0.1:8000/api/rooms";

    const method = editingRoom ? "PUT" : "POST";

    const response = await fetch(url, {
      method,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        name,
        capacity: Number(capacity),
      }),
    });

    if (response.ok) {
      setName("");
      setCapacity("");
      setEditingRoom(null);
      fetchRooms();
    } else {
      alert("Erro ao salvar");
    }
  };

  const handleEdit = (room) => {
    setName(room.name);
    setCapacity(room.capacity);
    setEditingRoom(room);
  };

  const handleDelete = async (id) => {
    const confirmDelete = confirm("Tem certeza que quer deletar essa sala?");

    if (!confirmDelete) return;

    const response = await fetch(`http://127.0.0.1:8000/api/rooms/${id}`, {
      method: "DELETE",
    });

    if (response.ok) {
      fetchRooms();
    } else {
      alert("Erro ao deletar sala");
    }
  };

  const handleReservation = async (e) => {
    e.preventDefault();

    const response = await fetch("http://127.0.0.1:8000/api/reservations", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        user_id: 1, // temporário
        room_id: Number(roomId),
        start_time: startTime,
        end_time: endTime,
      }),
    });

    if (response.status === 201) {
      setMessage("Reserva criada com sucesso!");
      setSuggestions([]);
    } else if (response.status === 409) {
      const data = await response.json();

      console.log("CONFLICT DATA:", data);

      setMessage(data.message);

      setSuggestions(data.available_rooms || []);
    } else if (response.status === 422) {
      const error = await response.json();
      console.log(error);
      setMessage("Dados inválidos");
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 flex items-center justify-center">
      <div className="w-full max-w-2xl bg-white p-6 rounded-2xl shadow-md">
        <h1 className="text-2xl font-bold mb-4 text-gray-800">Salas</h1>

        {/* FORM SALA */}
        <form onSubmit={handleSubmit} className="flex flex-col gap-3">
          <input
            type="text"
            placeholder="Nome da sala"
            value={name}
            onChange={(e) => setName(e.target.value)}
            className="border p-2 rounded-lg"
          />

          <input
            type="number"
            placeholder="Capacidade"
            value={capacity}
            onChange={(e) => setCapacity(e.target.value)}
            className="border p-2 rounded-lg"
          />

          <button className="bg-blue-500 text-white p-2 rounded-lg">
            {editingRoom ? "Atualizar Sala" : "Criar Sala"}
          </button>
        </form>

        {/* RESERVA */}
        <h2 className="mt-6 font-semibold">Criar Reserva</h2>

        <form onSubmit={handleReservation} className="flex flex-col gap-3 mt-2">
          <select
            value={roomId}
            onChange={(e) => setRoomId(e.target.value)}
            className="border p-2 rounded-lg"
          >
            <option value="">Selecione uma sala</option>
            {rooms.map((room) => (
              <option key={room.id} value={room.id}>
                {room.name}
              </option>
            ))}
          </select>

          <input
            type="datetime-local"
            value={startTime}
            onChange={(e) => setStartTime(e.target.value)}
            className="border p-2 rounded-lg"
          />

          <input
            type="datetime-local"
            value={endTime}
            onChange={(e) => setEndTime(e.target.value)}
            className="border p-2 rounded-lg"
          />

          <button className="bg-green-500 text-white p-2 rounded-lg">
            Criar Reserva
          </button>
        </form>

        {message && (
          <p
            className={`mt-3 p-2 rounded-lg text-sm ${
              message.includes("sucesso")
                ? "bg-green-100 text-green-700"
                : "bg-red-100 text-red-700"
            }`}
          >
            {message}
          </p>
        )}

        {message && message.includes("reservada") && (
          <div style={{ marginTop: "20px" }}>
            <h3>Salas disponíveis:</h3>

            {suggestions.length > 0 && (
              <div className="mt-4 p-4 bg-blue-50 rounded-lg">
                <h3 className="font-semibold text-blue-700 mb-2">
                  Sugestões disponíveis
                </h3>

                <ul className="space-y-2">
                  {suggestions.map((room) => (
                    <li
                      key={room.id}
                      className="flex justify-between items-center bg-white p-2 rounded shadow-sm"
                    >
                      <span>
                        {room.name} (cap: {room.capacity})
                      </span>

                      <button
                        onClick={() => setRoomId(room.id)}
                        className="bg-blue-500 text-white px-2 py-1 rounded"
                      >
                        Usar
                      </button>
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>
        )}

        {/* LISTA */}
        <ul className="mt-6 space-y-2">
          {rooms.map((room) => (
            <li
              key={room.id}
              className="flex justify-between items-center bg-gray-50 p-3 rounded-lg"
            >
              <span>
                {room.name} - {room.capacity}
              </span>

              <div className="flex gap-2">
                <button
                  onClick={() => handleEdit(room)}
                  className="bg-yellow-400 px-2 py-1 rounded"
                >
                  Editar
                </button>

                <button
                  onClick={() => handleDelete(room.id)}
                  className="bg-red-500 text-white px-2 py-1 rounded"
                >
                  Deletar
                </button>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}

export default App;
