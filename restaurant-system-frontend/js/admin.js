// Shared code for the admin pages (load it after app.js).

function getAdmin() {
    return JSON.parse(localStorage.getItem("admin") || "null");
}

function saveAdminSession(data) {
    localStorage.setItem("adminToken", data.token);
    localStorage.setItem("admin", JSON.stringify(data.user));
}

// Call at the top of every admin page except login and register.
function requireAdmin() {
    if (!localStorage.getItem("adminToken")) {
        location.href = "login.html";
        return false;
    }
    return true;
}

function renderSidebar() {
    const sidebar = document.getElementById("sidebar");
    if (!sidebar) return;

    const page = location.pathname.split("/").pop();
    const admin = getAdmin();
    const links = [
        ["Dashboard", "dashboard.html"],
        ["Orders", "orders.html"],
        ["Menu", "menu.html"],
        ["Reservations", "reservations.html"],
        ["Customers", "customers.html"],
    ];

    const items = links
        .map(([name, file]) => `
            <a href="${file}" class="whitespace-nowrap rounded-xl px-4 py-2 font-semibold ${page === file ? "bg-brand text-white" : "text-white/80 hover:bg-white/10"}">${name}</a>`)
        .join("");

    sidebar.innerHTML = `
    <div class="flex items-center gap-3 p-4 md:flex-col md:items-stretch md:gap-2">
        <a href="dashboard.html" class="hidden rounded-xl bg-white p-2 md:block"><img src="../images/logo.png" alt="ChopOps"></a>
        <nav class="flex flex-1 gap-1 overflow-x-auto md:flex-col md:overflow-visible">${items}</nav>
        <div class="hidden border-t border-white/10 pt-3 text-sm md:block">
            <p class="text-white/60">Signed in as</p>
            <p class="font-bold">${esc(admin ? admin.name : "Admin")}</p>
        </div>
        <a href="../index.html" class="hidden text-sm text-white/70 hover:text-white md:block">View website</a>
        <button id="admin-logout" class="rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold hover:bg-white/20">Logout</button>
    </div>`;

    document.getElementById("admin-logout").addEventListener("click", () => logout(true));
}

renderSidebar();
