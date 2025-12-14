<?php
require_once __DIR__ . '/../Repositories/PartyRepository.php';

/**
 * DashboardController
 * Gère l'affichage du tableau de bord
 */
class DashboardController
{
    private PartyRepository $partyRepository;

    public function __construct(PartyRepository $partyRepository)
    {
        $this->partyRepository = $partyRepository;
    }

    /**
     * Affiche le tableau de bord avec les parties en cours
     */
    public function index()
    {
        session_start();
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        
        // Récupérer les parties en brouillon avec le nombre de personnages
        $parties = $this->partyRepository->findDraftsWithCharacterCount($userId);
        
        // Charger la vue
        require_once __DIR__ . '/../views/dashboard.php';
    }
}
