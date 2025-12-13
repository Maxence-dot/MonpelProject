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

    <footer>
        <a href="contact.html">Contact</a>
    </footer>

    <script>
        const games = [{
                name: 'Partie 1',
                date: 'Créée le 01/09'
            },
            {
                name: 'Partie 2',
                date: 'Créée le 05/09'
            },
            {
                name: 'Partie 3',
                date: 'Créée le 10/09'
            },
            {
                name: 'Partie 4',
                date: 'Créée le 12/09'
            }
        ];

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

        function createGame() {
            alert('Redirection vers la création de partie');
            // window.location.href = 'create-game.html';
        }

        renderCarousel();
    </script>

    <?php
    $stmt = $pdo->prepare("SELECT * FROM session WHERE email = ?");
    $stmt->execute(["test@test.fr"]);
    $user = $stmt->fetch();

    var_dump($user)
    ?>

</body>

</html>