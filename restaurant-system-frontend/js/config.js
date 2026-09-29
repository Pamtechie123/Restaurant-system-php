// Address of the PHP backend. Change this if your server runs somewhere else.
const SERVER = "http://localhost:8000";
const API = SERVER + "/api";

// Tailwind colours and font used on every page
tailwind.config = {
    theme: {
        extend: {
            colors: {
                brand: { DEFAULT: "#f26b0c", dark: "#e04e0a", light: "#fff1e6" },
                ink: "#2b120c",
                cream: "#fff7f1",
            },
            fontFamily: { sans: ["Nunito", "system-ui", "sans-serif"] },
        },
    },
};
