import axios from "axios";

export default axios.create({
    baseURL: "https://battleship.blaeul.de/",
    headers: {
        "Content-type": "application/json"
    },

    // Cookies senden:
    withCredentials: true
});
