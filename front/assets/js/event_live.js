const id = new URLSearchParams(window.location.search).get("event_id");

    fetch(`/esportify/back/controllers/get_event_info.php?event_id=${id}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) return;

        const event = data.event;

        document.getElementById("eventTitle").textContent = event.titre;
        document.getElementById("eventImg").src = "/esportify/front/assets/events/" + event.image_url;
        document.getElementById("eventGame").textContent = event.jeu;
        document.getElementById("eventDate").textContent = event.date_event + " à " + event.heure_event;
        document.getElementById("eventDesc").textContent = event.description;

        const etatText = {
          "attente": "⏳ Événement en attente",
          "en_cours": "🎮 Événement en cours",
          "termine": "🏁 Événement terminé"
        };
        document.getElementById("etatEvent").textContent = etatText[event.etat] || "État inconnu";

        if ((data.role === "admin" || data.role === "organisateur") && event.etat === "en_cours") {
          document.getElementById("adminActions").innerHTML = `
            <a href="/esportify/back/pages/dashboard.php?changer_etat=termine&event_id=${id}">
              <button class="btn-delete">Terminer l’événement</button>
            </a>`;
        }
      });