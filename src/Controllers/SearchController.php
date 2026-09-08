<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Course;
use App\Services\SearchService;

/**
 * Controller handling global academic search and students-by-course search.
 */
class SearchController
{
    private SearchService $service;

    public function __construct()
    {
        $this->service = new SearchService();
    }

    /**
     * GET /search
     */
    public function index(): void
    {
        $query      = isset($_GET['q']) ? trim((string) $_GET['q']) : (isset($_GET['query']) ? trim((string) $_GET['query']) : '');
        $type       = isset($_GET['type']) ? trim((string) $_GET['type']) : 'all';
        $courseId   = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int) $_GET['course_id'] : null;

        $courses    = Course::findAll();
        $students   = [];
        $courseHits = [];
        $selectedCourse = null;
        $courseStudents = [];

        // If specific course lookup is requested
        if ($courseId !== null) {
            $courseLookup   = $this->service->getStudentsByCourse($courseId);
            $selectedCourse = $courseLookup['course'];
            $courseStudents = $courseLookup['students'];
        }

        // If a search query is provided
        if ($query !== '') {
            if ($type === 'student_id') {
                $students = $this->service->searchStudentsById($query);
            } elseif ($type === 'student_name') {
                $students = $this->service->searchStudentsByName($query);
            } elseif ($type === 'course_code') {
                $courseHits = $this->service->searchCoursesByCode($query);
            } elseif ($type === 'students_by_course') {
                // If query is a course code
                $courseLookup   = $this->service->getStudentsByCourse($query);
                $selectedCourse = $courseLookup['course'];
                $courseStudents = $courseLookup['students'];
            } else {
                $results    = $this->service->globalSearch($query, 'all');
                $students   = $results['students'];
                $courseHits = $results['courses'];
            }
        }

        // Handle JSON response if requested
        $isJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
        if ($isJson) {
            header('Content-Type: application/json');
            echo json_encode([
                'query'          => $query,
                'type'           => $type,
                'students'       => array_map(fn($s) => $s->toArray(), $students),
                'courses'        => array_map(fn($c) => $c->toArray(), $courseHits),
                'courseStudents' => array_map(fn($s) => $s->toArray(), $courseStudents),
            ]);
            return;
        }

        require __DIR__ . '/../../views/search/index.php';
    }
}
