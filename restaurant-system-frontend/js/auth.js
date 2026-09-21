
const API_URL = "http://localhost:8000/api";

const loginForm = document.getElementById("login-form");

loginForm.addEventListener("submit", async (event) => {

    event.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const message = document.getElementById("login-message");

    try {

        const response = await fetch(
            `${API_URL}/auth/login`,
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    email: email,
                    password: password
                })
            }
        );

        const data = await response.json();

        if (!response.ok) {

            throw new Error(
                data.error || "Login failed"
            );

        }

        // Save JWT
        localStorage.setItem(
            "token",
            data.token
        );

        // Save logged-in user information
        if (data.user) {
            localStorage.setItem(
                "user",
                JSON.stringify(data.user)
            );
        }

        message.textContent = "Login successful!";

        message.className =
            "mt-5 text-center text-sm text-green-600";


        setTimeout(() => {

            /*
             * ADMIN
             * Send administrators to the admin dashboard.
             */
            if (data.user && data.user.role === "admin") {

                window.location.href =
                    "../admin/dashboard.html";

                return;
            }


            /*
             * STAFF
             * Staff can also use the management area.
             */
            if (data.user && data.user.role === "staff") {

                window.location.href =
                    "../admin/dashboard.html";

                return;
            }


            /*
             * CUSTOMER
             * Normal customers go to the main website.
             */
            window.location.href =
                "../index.html";

        }, 800);


    } catch (error) {

        message.textContent =
            error.message;

        message.className =
            "mt-5 text-center text-sm text-red-600";

    }

});
