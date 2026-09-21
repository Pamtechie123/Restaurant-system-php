const API_BASE_URL = "http://localhost:8000/api";


async function getMenu() {

    const response = await fetch(`${API_BASE_URL}/menu`);

    if (!response.ok) {
        throw new Error("Failed to load menu");
    }

    return await response.json();
}