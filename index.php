<?php
require_once 'connexionAll.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accueil – Créer une partie</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="assets/css/modal.css" />
</head>

<body>

    <header>
        <h1>Mon jeu</h1>
        <button class="btn-create" onclick="createGame()">Créer une partie</button>
    </header>

    <main>
        <div class="carousel-container">
            <h2>Vos parties récentes</h2>
            <div class="carousel" id="carousel"></div>
        </div>
    </main>

    <!-- Step 1 Modal (hidden by default) -->
    <div id="step1Modal" class="modal" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="step1Title">
            <button class="close-btn" onclick="Modal.close('step1Modal')" aria-label="Fermer">✕</button>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
                <img src="assets/icons/knife.svg" alt="knife" width="46" height="46" style="border-radius:10px;background:linear-gradient(180deg,#ede8ff,#efeaff);padding:8px;" />
                <div>
                    <h2 id="step1Title">Créer une partie</h2>
                    <div style="color:#666;font-size:13px">Choisissez le type de jeu, un thème et un bref synopsis</div>
                </div>
            </div>
            <form id="step1Form" onsubmit="event.preventDefault(); submitStep1()">
                <label for="gameTypeSelect">Type de jeu</label>
                <select id="gameTypeSelect" required>
                    <option value="">Chargement...</option>
                </select>

                <label for="themeInput">Thème</label>
                <input id="themeInput" type="text" maxlength="255" placeholder="Ex: Mariage dans la campagne" />

                <label for="synopsisInput">Synopsis (optionnel)</label>
                <textarea id="synopsisInput" rows="4" placeholder="Décrivez brièvement le contexte..."></textarea>

                <div class="form-actions">
                    <button id="step1NextBtn" class="btn-primary" type="submit">Créer la partie</button>
                    <button type="button" class="btn-muted" onclick="Modal.close('step1Modal')">Annuler</button>
                </div>
                <div id="step1Message" class="status" role="status"></div>
            </form>
        </div>
    </div>

    <footer>
        <a href="contact.html">Contact</a>
    </footer>

    <script src="assets/js/modal.js" defer></script>
    <script>
        const games = [];
        let currentPartyId = null;

        function renderCarousel() {
            const carousel = document.getElementById('carousel');
            carousel.innerHTML = '';

            games.slice(0, 3).forEach(game => {
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `
          <div class="card-title">${game.name}</div>
          <div class="card-subtitle">${game.date}</div>
        `;
                carousel.appendChild(card);
            });
        }

        async function createGame() {
            Modal.open('step1Modal');
            try {
                await Modal.fetchGameTypes('#gameTypeSelect', '/api/game_types.php');
                // focus the select after it is populated
                setTimeout(() => {
                    const sel = document.getElementById('gameTypeSelect');
                    if (sel) sel.focus();
                }, 60);
            } catch (e) {
                Modal.setStatus('step1Modal', 'Erreur de chargement des types de jeu', 'error');
            }
        }

        // loadGameTypes, open/close helpers replaced by Modal helper


        async function submitStep1() {
            const sel = document.getElementById('gameTypeSelect');
            const theme = document.getElementById('themeInput').value.trim();
            const synopsis = document.getElementById('synopsisInput').value.trim();

            const payload = {
                game_type_id: parseInt(sel.value || 0, 10),
                theme,
                synopsis
            };

            if (!payload.game_type_id) {
                Modal.setStatus('step1Modal', 'Veuillez choisir un type de jeu.', 'error');
                return;
            }

            Modal.setStatus('step1Modal', '');
            Modal.setBusy('step1Modal', true, { busyText: 'Création en cours…' });

            try {
                const res = await fetch('/api/party.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    currentPartyId = data.id;
                    Modal.setStatus('step1Modal', `Partie initialisée (ID: ${data.id})`, 'success');
                    setTimeout(() => Modal.close('step1Modal'), 1200);
                } else {
                    Modal.setStatus('step1Modal', 'Erreur: ' + (data.error || 'inconnue'), 'error');
                    Modal.setBusy('step1Modal', false, { readyText: 'Créer la partie' });
                }
            } catch (e) {
                Modal.setStatus('step1Modal', 'Erreur réseau', 'error');
                Modal.setBusy('step1Modal', false, { readyText: 'Créer la partie' });
            }
        }

        renderCarousel();
    </script>


</body>

</html>