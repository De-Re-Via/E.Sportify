document.addEventListener("DOMContentLoaded", async () => {
  const content = document.getElementById("dashboardContent");

  try {
    const response = await fetch("/esportify/back/pages/dashboard.php");
    const html = await response.text();

    content.innerHTML = html;
  } catch (err) {
    content.innerHTML = "<p>Erreur lors du chargement du tableau de bord.</p>";
  }
});
