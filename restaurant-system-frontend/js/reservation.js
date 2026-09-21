const form =
    document.getElementById("reservation-form");


form.addEventListener("submit", async (event) => {

    event.preventDefault();


    const token =
        localStorage.getItem("token");


    if (!token) {

        alert("Please login first.");

        window.location.href =
            "login.html";

        return;
    }


    const data = {

        customerName:
            document.getElementById("customerName").value,

        phone:
            document.getElementById("phone").value,

        date:
            document.getElementById("date").value,

        time:
            document.getElementById("time").value,

        partySize:
            Number(
                document.getElementById("partySize").value
            )
    };


    const message =
        document.getElementById(
            "reservation-message"
        );


    try {

        const response = await fetch(
            "http://localhost:8000/api/reservations",
            {

                method: "POST",

                headers: {

                    "Content-Type":
                        "application/json",

                    "Authorization":
                        `Bearer ${token}`
                },

                body: JSON.stringify(data)

            }
        );


        const result =
            await response.json();


        if (!response.ok) {

            throw new Error(
                result.error ||
                "Reservation failed"
            );

        }


        message.textContent =
            "Reservation submitted successfully!";

        message.className =
            "text-center text-sm text-green-600";


        form.reset();


    } catch (error) {

        message.textContent =
            error.message;

        message.className =
            "text-center text-sm text-red-600";

    }

});