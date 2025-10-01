<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="container">
        <h2>Login</h2>

        <div id="alert" class="alert d-none"></div>

        <form id="loginForm">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="mt-4">
            <button id="checkMe" class="btn btn-success d-none">Check Profile</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const API_BASE = "/api"; // ✅ semua endpoint API ada di sini

        document.getElementById("loginForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            let email = document.getElementById("email").value;
            let password = document.getElementById("password").value;

            try {
                let res = await axios.post("/api/auth/login", { email, password });

                let token = res.data.access_token;
                localStorage.setItem("token", token);

                showAlert("Login berhasil!", "success");
                document.getElementById("checkMe").classList.remove("d-none");

            } catch (err) {
                console.error(err.response?.data || err.message);
                showAlert("Login gagal! Periksa email atau password.", "danger");
            }
        });

        // 🔹 Cek profil dengan token yang tersimpan
        document.getElementById("checkMe").addEventListener("click", async function() {
            let token = localStorage.getItem("token");

            try {
                let res = await axios.get(`${API_BASE}/auth/me`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                alert("Halo, " + res.data.name + " (role: " + res.data.role + ")");
            } catch (err) {
                console.error(err.response?.data || err.message);
                alert("Gagal ambil data user. Mungkin token expired?");
            }
        });

        function showAlert(message, type) {
            let alertBox = document.getElementById("alert");
            alertBox.className = "alert alert-" + type;
            alertBox.innerText = message;
            alertBox.classList.remove("d-none");
        }
    </script>
</body>
</html>
