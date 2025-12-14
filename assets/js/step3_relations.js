// Gestion de l'Étape 3 : Relations et Backgrounds

// Cache pour les relations de chaque personnage
const relationsCache = {};

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    renderCharacters();
    loadAllRelations();
});

// Afficher tous les personnages
function renderCharacters() {
    const grid = document.getElementById('charactersGrid');
    grid.innerHTML = '';

    characters.forEach(character => {
        const card = createCharacterCard(character);
        grid.appendChild(card);
    });
}

// Créer une carte personnage
function createCharacterCard(character) {
    const div = document.createElement('div');
    div.className = 'character-card';
    div.dataset.characterId = character.id;

    div.innerHTML = `
        <h3>
            <input type="text" 
                   value="${escapeHtml(character.firstname)}" 
                   onchange="updateCharacterName(${character.id}, this.value)"
                   placeholder="Prénom">
            <button class="btn-delete-char" onclick="deleteCharacter(${character.id})">✖</button>
        </h3>

        <div class="background-section">
            <label>Background / Histoire</label>
            <textarea 
                onchange="updateCharacterBackground(${character.id}, this.value)"
                placeholder="Décrivez l'histoire de ce personnage..."
            >${escapeHtml(character.background || '')}</textarea>
        </div>

        <div class="relations-section">
            <h4>
                Relations
                <button class="btn-add-relation" onclick="openAddRelationModal(${character.id})" title="Ajouter une relation">+</button>
            </h4>
            <div id="relations-${character.id}" class="relations-list">
                <div class="empty-relations">Chargement...</div>
            </div>
        </div>
    `;

    return div;
}

// Échapper le HTML pour éviter les injections XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Mettre à jour le prénom d'un personnage
async function updateCharacterName(characterId, newName) {
    try {
        const response = await fetch(basePath + '/api/characters/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                character_id: characterId,
                firstname: newName
            })
        });

        const data = await response.json();
        if (!data.success) {
            alert('Erreur: ' + data.error);
        } else {
            // Mettre à jour le cache local
            const char = characters.find(c => c.id === characterId);
            if (char) char.firstname = newName;
        }
    } catch (error) {
        alert('Erreur lors de la mise à jour: ' + error.message);
    }
}

// Mettre à jour le background d'un personnage
async function updateCharacterBackground(characterId, background) {
    try {
        const response = await fetch(basePath + '/api/characters.php?action=update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                character_id: characterId,
                background: background
            })
        });

        const data = await response.json();
        if (!data.success) {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        alert('Erreur lors de la mise à jour: ' + error.message);
    }
}

// Supprimer un personnage
async function deleteCharacter(characterId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce personnage ?')) {
        return;
    }

    try {
        const response = await fetch(basePath + '/api/characters.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ character_id: characterId })
        });

        const data = await response.json();
        if (data.success) {
            // Supprimer du cache local
            characters = characters.filter(c => c.id !== characterId);
            renderCharacters();
            loadAllRelations();
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        alert('Erreur lors de la suppression: ' + error.message);
    }
}

// Ajouter un nouveau personnage
async function addNewCharacter() {
    const firstname = prompt('Prénom du nouveau personnage :');
    if (!firstname || firstname.trim() === '') return;

    try {
        const response = await fetch(basePath + '/api/characters.php?action=add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                party_id: partyId,
                firstname: firstname.trim()
            })
        });

        const data = await response.json();
        if (data.success) {
            characters.push(data.character);
            renderCharacters();
            loadAllRelations();
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        alert('Erreur lors de l\'ajout: ' + error.message);
    }
}

// Charger toutes les relations
async function loadAllRelations() {
    for (const character of characters) {
        await loadCharacterRelations(character.id);
    }
}

// Charger les relations d'un personnage
async function loadCharacterRelations(characterId) {
    try {
        const response = await fetch(`${basePath}/api/relations.php?action=get&character_id=${characterId}`);
        const data = await response.json();

        if (data.success) {
            relationsCache[characterId] = data.relations;
            renderRelations(characterId);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des relations:', error);
    }
}

// Afficher les relations d'un personnage
function renderRelations(characterId) {
    const container = document.getElementById(`relations-${characterId}`);
    if (!container) return;

    const relations = relationsCache[characterId] || [];

    if (relations.length === 0) {
        container.innerHTML = '<div class="empty-relations">Aucune relation définie</div>';
        return;
    }

    container.innerHTML = relations.map(relation => `
        <div class="relation-item">
            <div class="relation-content">
                <div class="relation-type">${escapeHtml(relation.relation_type)}</div>
                <div class="relation-target">→ ${escapeHtml(relation.target_firstname)}</div>
            </div>
            <button class="btn-delete-relation" onclick="deleteRelation(${relation.id}, ${characterId})" title="Supprimer">✖</button>
        </div>
    `).join('');
}

// Ouvrir la modale d'ajout de relation
function openAddRelationModal(characterId) {
    document.getElementById('currentCharacterId').value = characterId;
    
    // Remplir la liste des personnages cibles (tous sauf le personnage actuel)
    const select = document.getElementById('targetCharacter');
    select.innerHTML = '<option value="">Choisir un personnage...</option>';
    
    characters.forEach(char => {
        if (char.id !== characterId) {
            const option = document.createElement('option');
            option.value = char.id;
            option.textContent = char.firstname;
            select.appendChild(option);
        }
    });

    openModal('relationModal');
}

// Fermer la modale de relation
function closeRelationModal() {
    closeModal('relationModal');
    document.getElementById('relationForm').reset();
}

// Soumettre le formulaire de relation
document.getElementById('relationForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const characterId = parseInt(document.getElementById('currentCharacterId').value);
    const targetCharacterId = parseInt(document.getElementById('targetCharacter').value);
    const relationType = document.getElementById('relationType').value.trim();
    const description = document.getElementById('relationDescription').value.trim();

    try {
        const response = await fetch(basePath + '/api/relations.php?action=create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                character_id: characterId,
                target_character_id: targetCharacterId,
                relation_type: relationType,
                description: description || null
            })
        });

        const data = await response.json();
        if (data.success) {
            closeRelationModal();
            await loadCharacterRelations(characterId);
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        alert('Erreur lors de la création de la relation: ' + error.message);
    }
});

// Supprimer une relation
async function deleteRelation(relationId, characterId) {
    if (!confirm('Supprimer cette relation ?')) return;

    try {
        const response = await fetch(basePath + '/api/relations.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ relation_id: relationId })
        });

        const data = await response.json();
        if (data.success) {
            await loadCharacterRelations(characterId);
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        alert('Erreur lors de la suppression: ' + error.message);
    }
}

// Terminer la création de la partie
async function finishParty() {
    if (!confirm('Terminer la création de cette Murder Party ?')) return;

    try {
        const response = await fetch(basePath + '/api/party.php?action=finish', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ party_id: partyId })
        });

        const data = await response.json();
        if (data.success) {
            alert('Murder Party créée avec succès !');
            window.location.href = basePath + '/dashboard';
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}
