<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Connection;
use PDO;

/**
 * Represents a student address.
 *
 * Encapsulates database operations for the `addresses` table.
 */
class Address
{
    public function __construct(
        public ?int    $id        = null,
        public int           $studentId = 0,
        public string        $province  = '',
        public string        $district  = '',
        public string        $sector    = '',
        public string        $cell      = '',
        public ?string       $createdAt = null,
        public ?string       $updatedAt = null,
    ) {}

    /**
     * Get human-readable full address representation.
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([$this->cell, $this->sector, $this->district, $this->province]);
        return implode(', ', $parts);
    }

    /**
     * Find a single address by primary key.
     */
    public static function findById(int $id): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Find an address by student ID.
     */
    public static function findByStudentId(int $studentId): ?self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM addresses WHERE student_id = :student_id LIMIT 1');
        $stmt->execute([':student_id' => $studentId]);
        $row  = $stmt->fetch();
        return $row ? self::fromRow($row) : null;
    }

    /**
     * Create a new address row for a student.
     *
     * @param array{student_id: int, province: string, district: string, sector: string, cell: string} $data
     */
    public static function create(array $data): self
    {
        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO addresses (student_id, province, district, sector, cell)
             VALUES (:student_id, :province, :district, :sector, :cell)'
        );
        $stmt->execute([
            ':student_id' => $data['student_id'],
            ':province'   => trim($data['province'] ?? ''),
            ':district'   => trim($data['district'] ?? ''),
            ':sector'     => trim($data['sector'] ?? ''),
            ':cell'       => trim($data['cell'] ?? ''),
        ]);
        return self::findById((int) $pdo->lastInsertId());
    }

    /**
     * Update address fields on this instance.
     *
     * @param array{province?: string, district?: string, sector?: string, cell?: string} $data
     */
    public function update(array $data): self
    {
        $this->province = isset($data['province']) ? trim($data['province']) : $this->province;
        $this->district = isset($data['district']) ? trim($data['district']) : $this->district;
        $this->sector   = isset($data['sector'])   ? trim($data['sector'])   : $this->sector;
        $this->cell     = isset($data['cell'])     ? trim($data['cell'])     : $this->cell;

        $pdo  = Connection::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE addresses
                SET province = :province, district = :district, sector = :sector, cell = :cell
              WHERE id = :id'
        );
        $stmt->execute([
            ':province' => $this->province,
            ':district' => $this->district,
            ':sector'   => $this->sector,
            ':cell'     => $this->cell,
            ':id'       => $this->id,
        ]);
        return $this;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id:        (int) $row['id'],
            studentId: (int) $row['student_id'],
            province:        $row['province']   ?? '',
            district:        $row['district']   ?? '',
            sector:          $row['sector']     ?? '',
            cell:            $row['cell']       ?? '',
            createdAt:       $row['created_at'] ?? null,
            updatedAt:       $row['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'student_id'   => $this->studentId,
            'province'     => $this->province,
            'district'     => $this->district,
            'sector'       => $this->sector,
            'cell'         => $this->cell,
            'full_address' => $this->getFullAddress(),
            'created_at'   => $this->createdAt,
            'updated_at'   => $this->updatedAt,
        ];
    }
}
