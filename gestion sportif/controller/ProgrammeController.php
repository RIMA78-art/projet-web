<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../model/ProgrammeModel.php';

class ProgrammeController
{
    private ProgrammeModel $model;
    private string $action;

    public function __construct()
    {
        $this->model = new ProgrammeModel();
        $this->action = $_GET['action'] ?? 'front';
    }

    public function run(): void
    {
        switch ($this->action) {
            case 'add':
                $this->handleAdd();
                break;
            case 'edit':
                $this->handleEdit();
                break;
            case 'delete':
                $this->handleDelete();
                break;
            case 'list':
                $this->showBack();
                break;
            case 'front':
            default:
                $this->showFront();
                break;
        }
    }

    private function handleAdd(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->sanitizeFormData($_POST);

            if ($this->isValidProgramme($data)) {
                $this->model->addProgramme($data);
                $this->setFlash('Programme ajouté avec succès', 'success');
            } else {
                $this->setFlash('Tous les champs sont requis.', 'danger');
            }
        }

        $this->redirect('list');
    }

    private function handleEdit(): void
    {
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->sanitizeFormData($_POST);

            if ($id > 0 && $this->isValidProgramme($data)) {
                $this->model->updateProgramme($id, $data);
                $this->setFlash('Programme modifié', 'success');
                $this->redirect('list');
            }

            $this->setFlash('Impossible de modifier le programme.', 'danger');
            $this->redirect('list');
        }

        $programme = $this->model->getProgrammeById($id);
        $this->showBack($programme);
    }

    private function handleDelete(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $this->model->deleteProgramme($id);
            $this->setFlash('Programme supprimé', 'success');
        } else {
            $this->setFlash('Identifiant invalide pour la suppression.', 'danger');
        }

        $this->redirect('list');
    }

    private function showFront(): void
    {
        $filters = [
            'type' => trim($_GET['type'] ?? ''),
            'niveau' => trim($_GET['niveau'] ?? ''),
        ];

        $programmes = $this->model->getAllProgrammes($filters);
        $types = $this->model->getUniqueTypes();
        $niveaux = $this->model->getUniqueNiveaux();

        echo $this->renderView(__DIR__ . '/../view/front/programme_front.html', [
            'ALERT_MESSAGE' => $this->getFlash(),
            'PROGRAMME_CARDS' => $this->buildFrontCards($programmes),
            'TYPE_OPTIONS' => $this->buildFilterOptions($types, $filters['type']),
            'NIVEAU_OPTIONS' => $this->buildFilterOptions($niveaux, $filters['niveau']),
            'TYPE_FILTER' => htmlspecialchars($filters['type'], ENT_QUOTES, 'UTF-8'),
            'NIVEAU_FILTER' => htmlspecialchars($filters['niveau'], ENT_QUOTES, 'UTF-8'),
        ]);
    }

    private function showBack(?array $editProgramme = null): void
    {
        $programmes = $this->model->getAllProgrammes();
        $types = $this->model->getUniqueTypes();
        $niveaux = $this->model->getUniqueNiveaux();

        echo $this->renderView(__DIR__ . '/../view/back/programme_back.html', [
            'BACK_MESSAGE' => $this->getFlash(),
            'TABLE_ROWS' => $this->buildBackTable($programmes),
            'EDIT_FORM' => $editProgramme ? $this->buildEditForm($editProgramme) : '',
            'TYPE_OPTIONS' => $this->buildStaticOptions($types),
            'NIVEAU_OPTIONS' => $this->buildStaticOptions($niveaux),
        ]);
    }

    private function buildFrontCards(array $programmes): string
    {
        if (empty($programmes)) {
            return '<div class="alert alert-info">Aucun programme trouvé pour ces critères.</div>';
        }

        $cards = '';

        foreach ($programmes as $programme) {
            $cards .= sprintf(
                '<div class="col-md-6 col-lg-4 mb-4"><div class="card h-100 shadow-sm border-0"><div class="card-body d-flex flex-column"><div class="d-flex justify-content-between align-items-start mb-3"><h5 class="card-title">%s</h5><span class="badge bg-primary text-uppercase">%s</span></div><p class="mb-2"><strong>Durée :</strong> %s min</p><p class="mb-2"><strong>Niveau :</strong> <span class="badge bg-secondary">%s</span></p><p class="card-text mb-4">%s</p><div class="mt-auto d-flex justify-content-between align-items-center"><span class="text-muted fw-semibold">%s kcal</span><a href="#" class="btn btn-outline-primary btn-sm">Commencer</a></div></div></div></div>',
                htmlspecialchars($programme['nom'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($programme['type'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $programme['duree'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($programme['niveau'], ENT_QUOTES, 'UTF-8'),
                nl2br(htmlspecialchars($programme['description'], ENT_QUOTES, 'UTF-8')),
                htmlspecialchars((string) $programme['calories'], ENT_QUOTES, 'UTF-8')
            );
        }

        return $cards;
    }

    private function buildBackTable(array $programmes): string
    {
        if (empty($programmes)) {
            return '<tr><td colspan="6" class="text-center">Aucun programme disponible</td></tr>';
        }

        $rows = '';

        foreach ($programmes as $programme) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s min</td><td>%s</td><td>%s</td><td class="text-end"><a href="index.php?action=edit&id=%s" class="btn btn-sm btn-warning me-2">Modifier</a><a href="index.php?action=delete&id=%s" class="btn btn-sm btn-danger" onclick="return confirm(\'Voulez-vous vraiment supprimer ce programme ?\');">Supprimer</a></td></tr>',
                htmlspecialchars($programme['nom'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($programme['type'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $programme['duree'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($programme['niveau'], ENT_QUOTES, 'UTF-8'),
                nl2br(htmlspecialchars($programme['description'], ENT_QUOTES, 'UTF-8')),
                htmlspecialchars((string) $programme['id_programme'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $programme['id_programme'], ENT_QUOTES, 'UTF-8')
            );
        }

        return $rows;
    }

    private function buildEditForm(array $programme): string
    {
        return sprintf(
            '<div class="card shadow-sm border-0 mb-4"><div class="card-body"><h5 class="card-title mb-4">Modifier le programme</h5><form method="post" action="index.php?action=edit&id=%s">%s%s%s%s%s%s%s%s%s%s</form></div></div>',
            htmlspecialchars((string) $programme['id_programme'], ENT_QUOTES, 'UTF-8'),
            $this->buildFormField('nom', 'Nom', $programme['nom']),
            $this->buildFormField('type', 'Type', $programme['type']),
            $this->buildFormField('duree', 'Durée (minutes)', (string) $programme['duree'], 'number', 'min="1"'),
            $this->buildFormField('niveau', 'Niveau', $programme['niveau']),
            $this->buildTextAreaField('description', 'Description', $programme['description']),
            $this->buildFormField('calories', 'Calories', (string) $programme['calories'], 'number', 'min="0"'),
            '<div class="d-flex justify-content-end"><button type="submit" class="btn btn-success">Enregistrer</button></div>',
            '',
            '',
            ''
        );
    }

    private function buildFormField(string $name, string $label, string $value = '', string $type = 'text', string $extra = ''): string
    {
        return sprintf(
            '<div class="mb-3"><label class="form-label" for="%s">%s</label><input type="%s" class="form-control" id="%s" name="%s" value="%s" %s required></div>',
            $name,
            $label,
            $type,
            $name,
            $name,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            $extra
        );
    }

    private function buildTextAreaField(string $name, string $label, string $value = ''): string
    {
        return sprintf(
            '<div class="mb-3"><label class="form-label" for="%s">%s</label><textarea class="form-control" id="%s" name="%s" rows="4" required>%s</textarea></div>',
            $name,
            $label,
            $name,
            $name,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        );
    }

    private function buildFilterOptions(array $options, string $selected): string
    {
        $html = '<option value="">Tous</option>';

        foreach ($options as $option) {
            $isSelected = $option === $selected ? ' selected' : '';
            $html .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars($option, ENT_QUOTES, 'UTF-8'), $isSelected, htmlspecialchars($option, ENT_QUOTES, 'UTF-8'));
        }

        return $html;
    }

    private function buildStaticOptions(array $options): string
    {
        $html = '';

        foreach ($options as $option) {
            $html .= sprintf('<option value="%s">%s</option>', htmlspecialchars($option, ENT_QUOTES, 'UTF-8'), htmlspecialchars($option, ENT_QUOTES, 'UTF-8'));
        }

        return $html;
    }

    private function sanitizeFormData(array $data): array
    {
        return [
            'nom' => trim($data['nom'] ?? ''),
            'type' => trim($data['type'] ?? ''),
            'duree' => (string) max(0, (int) ($data['duree'] ?? 0)),
            'niveau' => trim($data['niveau'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'calories' => (string) max(0, (int) ($data['calories'] ?? 0)),
        ];
    }

    private function isValidProgramme(array $data): bool
    {
        return $data['nom'] !== '' && $data['type'] !== '' && $data['duree'] !== '0' && $data['niveau'] !== '' && $data['description'] !== '';
    }

    private function setFlash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    private function getFlash(): string
    {
        $message = '';

        if (!empty($_SESSION['flash_message'])) {
            $type = $_SESSION['flash_type'] ?? 'info';
            $message = sprintf('<div class="alert alert-%s alert-dismissible fade show" role="alert">%s<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>',
                htmlspecialchars($type, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($_SESSION['flash_message'], ENT_QUOTES, 'UTF-8')
            );
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        }

        return $message;
    }

    private function redirect(string $action, array $parameters = []): void
    {
        $query = http_build_query(array_merge(['action' => $action], $parameters));
        header('Location: index.php?' . $query);
        exit;
    }

    private function renderView(string $templatePath, array $values = []): string
    {
        $template = file_get_contents($templatePath);

        foreach ($values as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }
}
