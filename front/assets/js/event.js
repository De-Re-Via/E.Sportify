// events.js – utilisé dans dashboard.html
// Gère : création, affichage perso, modération admin/orga

// Ouvre/ferme le modal
function openEventModal() {
  document.getElementById("eventModal").style.display = "block";
}
function closeEventModal() {
  document.getElementById("eventModal").style.display = "none";
}

// Soumission formulaire de création
document.addEventListener("DOMContentLoaded", () => {
  const eventForm = document.getElementById("eventForm");
  if (eventForm) {
    eventForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const formData = new FormData(eventForm);
      try {
        const response = await fetch("/esportify/back/controllers/create_event.php", {
          method: "POST",
          body: formData
        });
        const text = await response.text();
        if (text.includes("succès")) {
          alert("Événement proposé !");
          eventForm.reset();
          closeEventModal();
        } else {
          alert(text);
        }
      } catch {
        alert("Erreur lors de la création.");
      }
    });
  }
});

// Chargement des événements personnels
async function loadMyEvents() {
  const container = document.getElementById("eventsList");
  if (!container) return;
  try {
    const response = await fetch("/esportify/back/pages/my_events.php");
    const html = await response.text();
    container.innerHTML = html;
  } catch {
    container.innerHTML = "<p>Erreur chargement.</p>";
  }
}

// Modération des événements (valider/refuser)
async function validerEvent(id) {
  if (!confirm("Valider cet événement ?")) return;
  const formData = new FormData();
  formData.append("event_id", id);
  formData.append("action", "valide");

  const res = await fetch("/esportify/back/pages/manage_events.php", { method: "POST", body: formData });
  const txt = await res.text();
  if (txt === "ok") {
    alert("Validé !");
    loadModeration();
  } else alert(txt);
}

async function refuserEvent(id) {
  if (!confirm("Refuser cet événement ?")) return;
  const formData = new FormData();
  formData.append("event_id", id);
  formData.append("action", "refuse");

  const res = await fetch("/esportify/back/pages/manage_events.php", { method: "POST", body: formData });
  const txt = await res.text();
  if (txt === "ok") {
    alert("Refusé.");
    loadModeration();
  } else alert(txt);
}

// Chargement modération
async function loadModeration() {
  const container = document.getElementById("moderationZone");
  if (!container) return;
  try {
    const res = await fetch("/esportify/back/pages/manage_events.php");
    const html = await res.text();
    container.innerHTML = html;
  } catch {
    container.innerHTML = "<p>Erreur modération.</p>";
  }
}

// Initialisation
document.addEventListener("DOMContentLoaded", () => {
  loadMyEvents();
  loadModeration();
});
