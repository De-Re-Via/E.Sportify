// dashboard.js - version stable validée
// Gère la création d'événement, le dashboard dynamique et la modale points

let soumissionEnCours = false;

document.addEventListener("DOMContentLoaded", async () => {
  const content = document.getElementById("dashboardContent");
  try {
    const response = await fetch("/esportify/back/pages/dashboard.php");
    const html = await response.text();
    content.innerHTML = html;
    activerAttributionPoints();
  } catch (err) {
    content.innerHTML = "<p>Erreur chargement dashboard.</p>";
    console.error("Erreur JS :", err);
  }

  const eventForm = document.getElementById("eventForm");
  if (eventForm) {
    eventForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (soumissionEnCours) return;
      soumissionEnCours = true;

      const formData = new FormData(eventForm);
      try {
        const res = await fetch("/esportify/back/controllers/create_event.php", {
          method: "POST",
          body: formData
        });
        const text = await res.text();
        console.log("Réponse création :", text);
        if (res.ok) {
          alert("Événement proposé avec succès.");
          closeEventModal();
          eventForm.reset();
          await actualiserDashboard();
        } else {
          alert("Erreur création : " + text);
        }
      } catch (err) {
        alert("Erreur JS : " + err.message);
      } finally {
        soumissionEnCours = false;
      }
    });
  }
});

async function actualiserDashboard() {
  try {
    const res = await fetch("/esportify/back/pages/dashboard.php");
    const html = await res.text();
    const container = document.getElementById("dashboardContent");
    container.innerHTML = html;
    activerAttributionPoints();
  } catch (err) {
    console.error("Erreur rechargement dashboard :", err);
  }
}

function activerAttributionPoints() {
  const buttons = document.querySelectorAll("button[onclick^='openPointsModal']");
  buttons.forEach(button => {
    const id_event = button.getAttribute("onclick").match(/\d+/)[0];
    button.addEventListener("click", () => openPointsModal(id_event));
  });
}

function openPointsModal(id_event) {
  document.getElementById("pointsEventId").value = id_event;

  fetch("/esportify/back/controllers/get_participants.php?id_event=" + id_event)
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById("playersList");
      container.innerHTML = "";
      if (!data || data.length === 0) {
        container.innerHTML = "<p>Aucun participant.</p>";
      } else {
        data.forEach(player => {
          container.innerHTML += `
            <div>
              <strong>${player.username}</strong><br>
              <input type="hidden" name="id[]" value="${player.id_user}" />
              Classement :
              <input type="number" name="classement_${player.id_user}" min="1"
                onchange="calculerPoints(this, ${player.id_user})">
              Points :
              <input type="number" name="points_${player.id_user}" id="points_${player.id_user}" value="0" readonly>
            </div><hr>
          `;
        });
      }
      document.getElementById("pointsModal").style.display = "flex";
    });
}

function closePointsModal() {
  document.getElementById("pointsModal").style.display = "none";
}

function calculerPoints(input, userId) {
  const pointsInput = document.getElementById("points_" + userId);
  const classement = parseInt(input.value);
  let points = 0;
  if (classement === 1) points = 10;
  else if (classement === 2) points = 8;
  else if (classement === 3) points = 6;
  else if (classement === 4) points = 4;
  else if (classement >= 5) points = 2;
  pointsInput.value = points;
}

function openEventModal() {
  document.getElementById("eventModal").style.display = "flex";
}
function closeEventModal() {
  document.getElementById("eventModal").style.display = "none";
}
window.openEventModal = openEventModal;
window.closeEventModal = closeEventModal;
