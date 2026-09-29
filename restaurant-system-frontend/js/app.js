// Shared code for every customer page: talking to the API, login, cart and the navbar.

const ROOT = location.pathname.includes("/admin/") ? "../" : "";
let DISHES = []; // filled by loadDishes(), used by the "Add to cart" buttons

// ---------- Small helpers ----------

// Escape text before putting it inside HTML, so a name like <script> or "quoted" cannot break the page.
function esc(text) {
    return String(text ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function money(amount) {
    return "₦" + Number(amount).toLocaleString("en-NG");
}

function formatDate(value) {
    return new Date(value).toLocaleString("en-NG", { dateStyle: "medium", timeStyle: "short" });
}

// Reservation dates are saved as "2026-10-05"
function formatDay(value) {
    return new Date(String(value).slice(0, 10) + "T00:00").toLocaleDateString("en-NG", { dateStyle: "medium" });
}

function imageSrc(url) {
    if (!url) return ROOT + "images/placeholder.svg";
    return esc(url.startsWith("http") ? url : SERVER + url);
}

function badge(status) {
    return `<span class="badge badge-${esc(status)}">${esc(status)}</span>`;
}

function toast(message, isError = false) {
    const box = document.createElement("div");
    box.textContent = message;
    box.className =
        "fixed bottom-5 right-5 z-50 max-w-xs rounded-xl px-5 py-3 text-white shadow-lg " +
        (isError ? "bg-red-600" : "bg-ink");
    document.body.appendChild(box);
    setTimeout(() => box.remove(), 3000);
}

// ---------- Talking to the API ----------

// Admin routes (/admin/...) use the admin token, everything else uses the customer token.
async function api(path, { method = "GET", body } = {}) {
    const isAdmin = path.startsWith("/admin");
    const token = localStorage.getItem(isAdmin ? "adminToken" : "token");

    const headers = {};
    if (token) headers.Authorization = "Bearer " + token;

    let payload = body;
    if (body && !(body instanceof FormData)) {
        headers["Content-Type"] = "application/json";
        payload = JSON.stringify(body);
    }

    let response;
    try {
        response = await fetch(API + path, { method, headers, body: payload });
    } catch (error) {
        throw new Error("Cannot reach the server. Is the PHP backend running?");
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        // Token expired or account removed: send the person back to log in
        if (response.status === 401 && token && !path.endsWith("/login")) {
            logout(isAdmin);
        }
        throw new Error((data && data.error) || "Something went wrong");
    }

    return data;
}

async function loadDishes() {
    DISHES = await api("/menu");
    return DISHES;
}

// ---------- Login session ----------

function getUser() {
    return JSON.parse(localStorage.getItem("user") || "null");
}

function saveSession(data) {
    localStorage.setItem("token", data.token);
    localStorage.setItem("user", JSON.stringify(data.user));
}

function logout(isAdmin = false) {
    if (isAdmin) {
        localStorage.removeItem("adminToken");
        localStorage.removeItem("admin");
        location.href = ROOT + "admin/login.html";
    } else {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        location.href = ROOT + "login.html";
    }
}

// Call at the top of pages that need a logged-in customer.
function requireLogin() {
    if (!localStorage.getItem("token")) {
        location.href = "login.html?next=" + encodeURIComponent(location.pathname.split("/").pop() + location.search);
        return false;
    }
    return true;
}

// ---------- Cart (saved in the browser) ----------

function getCart() {
    return JSON.parse(localStorage.getItem("cart") || "[]");
}

function saveCart(cart) {
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartBadge();
}

function addToCart(dish, quantity = 1) {
    const cart = getCart();
    const line = cart.find((item) => item.id === dish.id);

    if (line) {
        line.quantity += quantity;
    } else {
        cart.push({ id: dish.id, name: dish.name, price: dish.price, imageUrl: dish.imageUrl, quantity });
    }

    saveCart(cart);
    toast(dish.name + " added to cart");
}

function cartTotal() {
    return getCart().reduce((sum, item) => sum + item.price * item.quantity, 0);
}

function updateCartBadge() {
    const count = getCart().reduce((sum, item) => sum + item.quantity, 0);
    const badgeEl = document.getElementById("cart-count");
    if (badgeEl) {
        badgeEl.textContent = count;
        badgeEl.classList.toggle("hidden", count === 0);
    }
}

// Any button with data-add="<dish id>" adds that dish to the cart
document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-add]");
    if (!button) return;

    const dish = DISHES.find((d) => d.id === button.dataset.add);
    if (dish) addToCart(dish);
});

// ---------- Dish card (used on the home and menu pages) ----------

function dishCard(dish) {
    const unavailable = dish.isAvailable === false;

    return `
    <article class="card flex flex-col overflow-hidden ${unavailable ? "opacity-70" : ""}">
        <a href="dish.html?id=${dish.id}" class="block aspect-[4/3] overflow-hidden bg-cream">
            <img src="${imageSrc(dish.imageUrl)}" alt="${esc(dish.name)}"
                 class="h-full w-full object-cover transition duration-300 hover:scale-105">
        </a>
        <div class="flex flex-1 flex-col gap-1 p-4">
            <div class="flex items-start justify-between gap-2">
                <h3 class="text-lg font-bold">${esc(dish.name)}</h3>
                ${unavailable ? '<span class="badge badge-cancelled">Unavailable</span>' : ""}
            </div>
            <p class="font-bold text-brand">${money(dish.price)}</p>
            <button class="btn btn-sm mt-2" data-add="${dish.id}" ${unavailable ? "disabled" : ""}>Add to cart</button>
        </div>
    </article>`;
}

// ---------- Navbar and footer ----------

function renderNavbar() {
    const nav = document.getElementById("navbar");
    if (!nav) return;

    const page = location.pathname.split("/").pop() || "index.html";
    const user = getUser();
    const links = [
        ["Home", "index.html"],
        ["Menu", "menu.html"],
        ["Reserve", "reservation.html"],
        ["My Orders", "orders.html"],
    ];

    const pills = links
        .map(([name, file]) => `<a href="${file}" class="pill ${page === file ? "active" : ""}">${name}</a>`)
        .join("");

    const account = user
        ? `<span class="hidden text-sm font-semibold sm:inline">Hi, ${esc(user.name.split(" ")[0])}</span>
           <button id="logout-btn" class="pill">Logout</button>`
        : `<a href="login.html" class="pill ${page === "login.html" || page === "register.html" ? "active" : ""}">Login / Sign up</a>`;

    nav.innerHTML = `
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <a href="index.html"><img src="images/logo.png" alt="ChopOps" class="h-12"></a>
        <nav class="order-3 flex w-full gap-2 overflow-x-auto md:order-2 md:w-auto">${pills}</nav>
        <div class="order-2 flex items-center gap-3 md:order-3">
            ${account}
            <a href="cart.html" class="relative rounded-full border-2 border-brand p-2 text-brand" aria-label="Cart">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                <span id="cart-count" class="absolute -right-2 -top-2 hidden h-5 min-w-5 rounded-full bg-brand px-1 text-center text-xs font-bold leading-5 text-white">0</span>
            </a>
        </div>
    </div>`;

    const logoutBtn = document.getElementById("logout-btn");
    if (logoutBtn) logoutBtn.addEventListener("click", () => logout());

    updateCartBadge();
}

function renderFooter() {
    const footer = document.getElementById("footer");
    if (!footer) return;

    footer.innerHTML = `
    <div class="mt-16 border-t border-orange-100 bg-cream py-8 text-center text-sm">
        <p class="font-bold tracking-wide">GOOD FOOD.....<span class="text-brand">GREAT MOOD</span></p>
        <p class="mt-1 text-ink/60">© ${new Date().getFullYear()} ChopOps</p>
    </div>`;
}

// A button with data-toggle="<input id>" shows or hides that password field
document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-toggle]");
    if (!button) return;

    const input = document.getElementById(button.dataset.toggle);
    input.type = input.type === "password" ? "text" : "password";
});

renderNavbar();
renderFooter();
