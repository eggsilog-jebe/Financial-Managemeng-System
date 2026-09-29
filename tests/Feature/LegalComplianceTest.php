<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class LegalComplianceTest extends TestCase
{
    /**
     * Test that the login page renders statutory compliance elements and modal triggers.
     */
    public function test_login_screen_renders_terms_and_privacy_controls(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Terms &amp; Conditions', false);
        $response->assertSee('Privacy Policy (RA 10173)', false);
        $response->assertSee('Acceptable Use Policy', false);
        $response->assertSee('Hospital Legal &amp; Regulatory Framework', false);
    }

    /**
     * Test that the standalone Terms and Conditions page renders successfully.
     */
    public function test_standalone_terms_page_renders_with_statutory_framework(): void
    {
        $response = $this->get(route('legal.terms'));

        $response->assertStatus(200);
        $response->assertSee('Hospital Financial Management System (HFMS) Terms and Conditions');
        $response->assertSee('Presidential Decree No. 1445');
        $response->assertSee('Government Accounting Manual');
        $response->assertSee('Republic Act No. 11463');
        $response->assertSee('Section 14');
        $response->assertSee('NPC Circular No. 2016-01');
    }

    /**
     * Test that the standalone Privacy Policy page renders successfully.
     */
    public function test_standalone_privacy_page_renders_with_dpa_mandates(): void
    {
        $response = $this->get(route('legal.privacy'));

        $response->assertStatus(200);
        $response->assertSee('Hospital Financial Management System (HFMS) Privacy Policy');
        $response->assertSee('Republic Act No. 10173');
        $response->assertSee('Section 13(f)');
        $response->assertSee('Section 13(e)');
        $response->assertSee('NPC Circular No. 2020-03');
        $response->assertSee('NPC Circular No. 2022-04');
        $response->assertSee('dpo@hospital.gov.ph');
    }
}
