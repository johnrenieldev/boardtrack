<?php
/**
 * BoardTrack — Testimonial Model
 * app/models/Testimonial.php
 */
class Testimonial extends Model
{
    protected string $table = 'testimonials';

    /**
     * Get all approved testimonials for the landing page
     */
    public function getApprovedTestimonials($limit = 10)
    {
        $sql = "
            SELECT t.*, u.name, u.email, r.room_number
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN tenants te ON t.tenant_id = te.id
            LEFT JOIN rooms r ON te.room_id = r.id
            WHERE t.is_approved = 1
            ORDER BY t.created_at DESC
            LIMIT :limit
        ";
        
        return $this->rawQuery($sql, ['limit' => $limit]);
    }

    /**
     * Get testimonials by user ID
     */
    public function getTestimonialsByUserId($userId)
    {
        $sql = "
            SELECT t.*
            FROM {$this->table} t
            WHERE t.user_id = :user_id
            ORDER BY t.created_at DESC
        ";
        
        return $this->rawQuery($sql, ['user_id' => $userId]);
    }

    /**
     * Create a new testimonial
     */
    public function createTestimonial($data)
    {
        return $this->insert([
            'user_id' => $data['user_id'],
            'tenant_id' => $data['tenant_id'],
            'rating' => $data['rating'],
            'review_text' => $data['review_text'],
            'is_approved' => 1
        ]);
    }

    /**
     * Approve a testimonial (admin/landlord only)
     */
    public function approveTestimonial($id)
    {
        return $this->update(['is_approved' => 1], ['id' => $id]);
    }

    /**
     * Delete a testimonial
     */
    public function deleteTestimonial($id)
    {
        return $this->delete($id);
    }

    /**
     * Check if user has already submitted a testimonial
     */
    public function hasUserSubmittedTestimonial($userId)
    {
        return $this->exists('user_id', $userId);
    }

    /**
     * Get all testimonials with tenant names for landlord review page
     */
    public function getAllWithTenantNames()
    {
        $sql = "
            SELECT t.*, u.name as tenant_name, u.email
            FROM {$this->table} t
            JOIN users u ON t.user_id = u.id
            ORDER BY t.is_approved ASC, t.created_at DESC
        ";
        
        return $this->rawQuery($sql);
    }
}
