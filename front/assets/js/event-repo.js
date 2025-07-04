// event-repo.js – utilisé dans events.html
// Gère : affichage public + filtres + suppression admin

// Chargement des événements avec filtres
async function chargerEvenements() {
  const form = document.getElementById("filterForm");
  const container = document.getElementById("eventsList");
  if (!form || !container) return;

  const formData = new FormData(form);

  try {
    const response = await fetch("/esportify/back/pages/all_events.php", {
      method: "POST",
      body: formData
    });
    const html = await response.text();
    container.innerHTML = html;
  } catch (err) {
    console.error("Erreur chargement :", err);
    container.innerHTML = "<p>Erreur lors du chargement des événements.</p>";
  }
}

// Suppression d’un événement (admin)
async function supprimerEvent(id) {
  if (!confirm("Supprimer cet événement ?")) return;

  const formData = new FormData();
  formData.append("delete_id", id);

  try {
    const response = await fetch("/esportify/back/pages/delete_event.php", {
      method: "POST",
      body: formData
    });
    const text = await response.text();
    if (text === "ok") {
      alert("Événement supprimé.");
      chargerEvenements();
    } else {
      alert(text);
    }
  } catch {
    alert("Erreur lors de la suppression.");
  }
}

// Initialisation de la page events.html
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("filterForm");
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      chargerEvenements();
    });
    chargerEvenements(); // Chargement initial
  }
});
