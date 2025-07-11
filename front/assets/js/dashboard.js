
// ✅ Chargement du contenu PHP dynamiquement dans la section HTML
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
});

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
