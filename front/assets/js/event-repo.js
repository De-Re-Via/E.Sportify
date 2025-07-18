
async function chargerEvenements() {
  const container = document.getElementById("eventsContainer");
  try {
    const response = await fetch("/esportify/back/pages/all_events.php");
    const html = await response.text();
    container.innerHTML = html;

    const cartes = container.querySelectorAll(".event-card");

    cartes.forEach((carte) => {
      const id = carte.dataset.eventId;

      const btnIns = carte.querySelector(".register-btn");
      if (btnIns) {
        btnIns.addEventListener("click", () => inscrireAEvent(id));
      }

      const btnDesins = carte.querySelector(".unregister-btn");
      if (btnDesins) {
        btnDesins.addEventListener("click", () => desinscrireAEvent(id));
      }
    });
  } catch (err) {
    console.error("Erreur lors du chargement des événements :", err);
    container.innerHTML = "<p>Erreur lors du chargement des événements.</p>";
  }
}

async function inscrireAEvent(id_event) {
  try {
    const response = await fetch("/esportify/back/controllers/register_event.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json"
  },
  body: JSON.stringify({ id_event })
});

    const result = await response.text();
    alert(result);
    chargerEvenements();
  } catch (err) {
    alert("Erreur lors de l'inscription.");
    console.error("Erreur fetch inscription :", err);
  }
}

async function desinscrireAEvent(id_event) {
  const confirmation = confirm("Souhaitez-vous vraiment vous désinscrire de cet événement ?");
  if (!confirmation) return;

  try {
    const response = await fetch("/esportify/back/controllers/unregister_event.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json"
  },
  body: JSON.stringify({ id_event })
});

    const result = await response.text();
    alert(result);
    chargerEvenements();
  } catch (err) {
    alert("Erreur lors de la désinscription.");
    console.error("Erreur fetch désinscription :", err);
  }
}

// Initialisation automatique au chargement
document.addEventListener("DOMContentLoaded", () => {
  chargerEvenements();
});
