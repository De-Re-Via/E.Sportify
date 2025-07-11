// event-repo.js
// ➤ Affiche la liste des événements publics (page events.html)
// ➤ Gère l'inscription ET la désinscription aux événements

// Fonction principale pour charger dynamiquement les événements
async function chargerEvenements() {
  const form = document.getElementById("filterForm");
  const container = document.getElementById("eventsList");
  if (!form || !container) return;

  const formData = new FormData(form);

  try {
    const response = await fetch("/esportify/back/pages/all_events.php", {
      method: "POST",
      body: formData,
    });

    const html = await response.text();
    container.innerHTML = html;

    // ➤ Activation des boutons après chargement dynamique

    // Boutons "S’inscrire"
    const boutonsInscription = container.querySelectorAll(".register-btn");
    boutonsInscription.forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.dataset.eventId;
        inscrireAEvent(id);
      });
    });

    // Boutons "Se désinscrire"
    const boutonsDesinscription = container.querySelectorAll(".unregister-btn");
    boutonsDesinscription.forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.dataset.eventId;
        desinscrireAEvent(id);
      });
    });

  } catch (err) {
    console.error("Erreur chargement événements :", err);
    container.innerHTML = "<p>Erreur lors du chargement des événements.</p>";
  }
}

// ➤ Fonction d'inscription à un événement
async function inscrireAEvent(id_event) {
  try {
    const response = await fetch("/esportify/back/controllers/register_event.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_event }),
    });

    const result = await response.text();
    alert(result);
    chargerEvenements(); // Recharge la liste après inscription

  } catch (err) {
    alert("Erreur lors de l'inscription.");
    console.error("Erreur fetch inscription :", err);
  }
}

// ➤ Fonction de désinscription à un événement (avec confirmation)
async function desinscrireAEvent(id_event) {
  const confirmation = confirm("Souhaitez-vous vraiment vous désinscrire de cet événement ?");
  if (!confirmation) return;

  try {
    const response = await fetch("/esportify/back/controllers/unregister_event.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_event }),
    });

    const result = await response.text();
    alert(result);
    chargerEvenements(); // Recharge la liste après désinscription

  } catch (err) {
    alert("Erreur lors de la désinscription.");
    console.error("Erreur fetch désinscription :", err);
  }
}


// ➤ Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("filterForm");
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      chargerEvenements();
    });
  }

  chargerEvenements();
});
