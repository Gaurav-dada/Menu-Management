document.addEventListener("DOMContentLoaded", () => {
    const search = document.getElementById("searchMenu");
    const cards = document.querySelectorAll(".menu-card");

    if (search) {
        search.addEventListener("input", () => {
            const value = search.value.toLowerCase();
            cards.forEach(card => {
                card.style.display = card.innerText.toLowerCase().includes(value) ? "" : "none";
            });
        });
    }

    document.querySelectorAll(".confirm-delete").forEach(link => {
        link.addEventListener("click", e => {
            if (!confirm("Are you sure you want to delete this record?")) {
                e.preventDefault();
            }
        });
    });
});
