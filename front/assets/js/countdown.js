// countdown.js
// Chrono vers l’événement le plus proche

async function loadCountdown() {
  try {
    const res = await fetch("/esportify/back/pages/next_event.php");
    const event = await res.json();

    const countdownEl = document.getElementById("countdown");
    if (!countdownEl) return;

    // Si aucun événement ne correspond
    if (!event || !event.date_event || !event.heure_event) {
      countdownEl.textContent = "Aucun événement à venir";
      return;
    }

    // Crée la date/heure cible du décompte
    const target = new Date(`${event.date_event}T${event.heure_event}`);
    const interval = setInterval(updateCountdown, 1000);

    function updateCountdown() {
      const now = new Date();
      const diff = target - now;

      if (diff <= 0) {
        countdownEl.textContent = "C’est en cours !";
        clearInterval(interval);
        return;
      }

      const s = Math.floor(diff / 1000) % 60;
      const m = Math.floor(diff / 60000) % 60;
      const h = Math.floor(diff / 3600000) % 24;
      const d = Math.floor(diff / 86400000);
      countdownEl.textContent = `${d} j ${h} h ${m} min ${s} s`;
    }

    updateCountdown();

  } catch (err) {
    const el = document.getElementById("countdown");
    if (el) el.textContent = "Erreur de chargement";
  }
}

document.addEventListener("DOMContentLoaded", loadCountdown);
