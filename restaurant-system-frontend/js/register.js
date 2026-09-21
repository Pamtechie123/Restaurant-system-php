const form = document.getElementById("register-form");

form.addEventListener("submit", async (event) => {

    event.preventDefault();

    const name =
        document.getElementById("name").value;

    const email =
        document.getElementById("email").value;

    const password =
        document.getElementById("password").value;

    const message =
        document.getElementById("register-message");

    try {

        const response = await fetch(
            "http://localhost:8000/api/auth/register",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    name,
                    email,
                    password
                })
            }
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.error || "Registration failed"
            );
        }

        message.textContent =
            "Account created successfully!";

        message.className =
            "mt-5 text-center text-sm text-green-600";

        setTimeout(() => {
            window.location.href = "login.html";
        }, 1000);

    } catch (error) {

        message.textContent = error.message;

        message.className =
            "mt-5 text-center text-sm text-red-600";
    }

});