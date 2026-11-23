<?php

namespace App\Tests\Smoke;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Basic checks that the public pages render and that protected areas
 * redirect anonymous visitors to the login page.
 */
class PublicPagesTest extends WebTestCase
{
    public function testLoginPageRenders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form input[name="email"]');
        self::assertSelectorExists('form input[name="password"]');
    }

    public function testRegisterPageRenders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('protectedUrls')]
    public function testProtectedPagesRedirectToLogin(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertResponseRedirects('/login');
    }

    public static function protectedUrls(): iterable
    {
        yield 'home'              => ['/'];
        yield 'student dashboard' => ['/student/dashboard'];
        yield 'teacher dashboard' => ['/teacher/dashboard'];
        yield 'admin dashboard'   => ['/admin/dashboard'];
    }
}
