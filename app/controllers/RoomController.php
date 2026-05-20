<?php
/**
 * BoardTrack — RoomController (Phase 1 Fixed/Completed)
 * Full room management for landlords.
 */
class RoomController extends Controller
{
    private object $roomModel;
    private object $auditLogModel;

    public function __construct()
    {
        $this->requireRole('landlord');
        $this->roomModel   = $this->model('Room');
        $this->auditLogModel = $this->model('AuditLog');
    }
    /**
     * LANDLORD: List all rooms
     */
    public function rooms(): void
    {
        $rooms = $this->roomModel->getAllWithOccupancy();
        $this->view('landlord/rooms', [
            'pageTitle' => 'Rooms — BoardTrack',
            'rooms'     => $rooms,
        ], 'landlord');
    }
    /**
     * LANDLORD: Add new room
     */
    public function addRoom(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/rooms');
        }
        $data = [
            'room_number'   => trim($_POST['room_number'] ?? ''),
            'floor'         => (int)($_POST['floor'] ?? 1),
            'room_type'     => $_POST['room_type'] ?? 'single',
            'max_occupants' => (int)($_POST['max_occupants'] ?? 1),
            'monthly_rent'  => (float)($_POST['monthly_rent'] ?? 0),
            'status'        => 'available',
            'description'   => trim($_POST['description'] ?? ''),
        ];
        if (empty($data['room_number']) || $data['monthly_rent'] <= 0) {
            $this->flash('error', 'Room number and valid rent required.');
            $this->redirect('landlord/rooms');
        }
        if ($data['max_occupants'] < 1) {
            $this->flash('error', 'Max occupants must be at least 1.');
            $this->redirect('landlord/rooms');
        }
        $roomId = $this->roomModel->insert($data);
        $this->auditLogModel->log(
            $_SESSION['user_id'], 'room_created', 'room', $roomId,
            null, $data, "Room {$data['room_number']} added"
        );
        $this->flash('success', "Room {$data['room_number']} created.");
        $this->redirect('landlord/rooms');
    }
    /**
     * LANDLORD: Edit room
     */
    public function editRoom(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('landlord/rooms');
        }
        $room = $this->roomModel->find($id);
        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('landlord/rooms');
        }
        $data = [
            'floor'         => (int)($_POST['floor'] ?? $room['floor']),
            'room_type'     => $_POST['room_type'] ?? $room['room_type'],
            'max_occupants' => (int)($_POST['max_occupants'] ?? $room['max_occupants']),
            'monthly_rent'  => (float)($_POST['monthly_rent'] ?? $room['monthly_rent']),
            'status'        => $_POST['status'] ?? $room['status'],
            'description'   => trim($_POST['description'] ?? $room['description']),
        ];
        if ($data['max_occupants'] < 1) { $this->flash('error', 'Max occupants must be at least 1.'); $this->redirect('landlord/rooms'); }
        if ($data['monthly_rent'] <= 0) { $this->flash('error', 'Monthly rent must be greater than zero.'); $this->redirect('landlord/rooms'); }
        $this->roomModel->update($data, ['id' => $id]);
        $this->auditLogModel->log(
            $_SESSION['user_id'], 'room_updated', 'room', $id,
            $room, $data, "Room {$room['room_number']} updated"
        );
        $this->flash('success', 'Room updated.');
        $this->redirect('landlord/rooms');
    }
    /**
     * LANDLORD: Legacy store() compatibility
     */
    public function store(): void
    {
        $this->addRoom();
    }
}
?>

