<?php

namespace Tests\Feature;

use App\Support\DemoReportData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_renders_every_subject_in_every_section(): void
    {
        $this->withoutVite();

        $response = $this->actingAs(User::factory()->create([
            'role' => 'student',
        ]))->get('/student/report');
        $response->assertOk();

        foreach (DemoReportData::forStudent()['subjects'] as $subject) {
            $response->assertSee($subject['name']);
            foreach (array_keys($subject['topics']) as $topic) {
                $response->assertSee($topic);
            }
        }

        foreach (DemoReportData::forStudent()['subjects'] as $subject) {
            $key = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $subject['name']), '-'));

            $response->assertSee('data-series="'.$key.'"', false);
            $response->assertSee('data-subject="'.$key.'"', false);
            $response->assertSee('data-subject-card');
        }

        $this->assertSame(1, preg_match_all('/data-subject-card[^>]*\sopen\s*>/', $response->getContent()));
        $this->assertStringContainsString('id="subject-accounting"', $response->getContent());
        $response->assertSee('Subjects');
        $response->assertSee('Assessment performance');
    }
}
