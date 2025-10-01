import './bootstrap';
import axios from 'axios';

const API_URL = "/api"; // biar otomatis ikut domain laravel

// === AUTO LOGIN CHECK ===
const token = localStorage.getItem("token");
if (token) {
  axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  showDashboard();
}

// === LOGIN ===
document.getElementById("loginForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  try {
    const res = await axios.post(`${API_URL}/auth/login`, {
      email: document.getElementById("email").value,
      password: document.getElementById("password").value,
    });

    const token = res.data.access_token;
    localStorage.setItem("token", token);
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

    showDashboard();
  } catch (err) {
    console.error(err.response?.data || err);
    alert("Login gagal! Periksa email/password.");
  }
});


// === LOGOUT ===
document.getElementById("logoutBtn")?.addEventListener("click", async () => {
  try {
    await axios.post(`${API_URL}/auth/logout`);
  } catch (e) {}
  localStorage.removeItem("token");
  location.reload();
});

// === SHOW DASHBOARD ===
function showDashboard() {
  document.getElementById("loginPage").classList.add("d-none");
  document.getElementById("dashboard").classList.remove("d-none");
  loadTasks();
}

// === LOAD TASKS ===
async function loadTasks() {
  try {
    const res = await axios.get(`${API_URL}/tasks`);
    const tbody = document.getElementById("taskTable");
    tbody.innerHTML = "";
    res.data.data.forEach((task) => {
      tbody.innerHTML += `
        <tr>
          <td>${task.title}</td>
          <td>${task.status}</td>
          <td>${task.priority}</td>
          <td>${task.assignee ? task.assignee.name : "-"}</td>
          <td>${task.due_date ?? "-"}</td>
          <td>
            <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.id})">Delete</button>
          </td>
        </tr>
      `;
    });
  } catch (err) {
    console.error(err);
    alert("Gagal load tasks!");
  }
}

// === CREATE TASK ===
document.getElementById("taskForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  try {
    await axios.post(`${API_URL}/tasks`, {
      title: document.getElementById("taskTitle").value,
      description: document.getElementById("taskDesc").value,
      priority: document.getElementById("taskPriority").value,
      due_date: document.getElementById("taskDue").value,
    });
    document.querySelector("#taskModal .btn-close").click();
    loadTasks();
  } catch (err) {
    console.error(err);
    alert("Gagal buat task!");
  }
});

// === DELETE TASK ===
async function deleteTask(id) {
  if (!confirm("Yakin hapus task ini?")) return;
  try {
    await axios.delete(`${API_URL}/tasks/${id}`);
    loadTasks();
  } catch (err) {
    alert("Gagal hapus task!");
  }
}
