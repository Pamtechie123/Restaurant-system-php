function requireLogin() {

    const token = localStorage.getItem("token");

    if (!token) {

        window.location.href =
            "login.html";

        return false;
    }

    return true;
}