<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\Payment\PaymentReceivedNotification;
use App\Notifications\SystemNotification;
use App\Services\NotificationDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    private ?Tenant $tenant = null;

    private string $tenantId = 'notifytest';

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection('mysql')->table('tenants')->where('id', $this->tenantId)->delete();
            DB::connection('mysql')->statement('DROP DATABASE IF EXISTS `tenantnotifytest`');
        } catch (\Throwable) {
            // A previous tenant may not exist yet.
        }

        Plan::query()->delete();

        Plan::create([
            'slug' => 'notification-plan',
            'name' => 'باقة اختبار الإشعارات',
            'price' => 99,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'accounting_advanced', 'pos', 'purchase', 'warehouses'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => 'notification-plan',
            'is_suspended' => false,
        ]);

        $this->tenant->domains()->create([
            'domain' => $this->tenantId.'.localhost',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                tenancy()->end();
                DB::disconnect('tenant');
                DB::purge('tenant');
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Throwable) {
                // The test database may already be unavailable after a failure.
            }
        }

        parent::tearDown();
    }

    public function test_bell_lists_only_important_notifications(): void
    {
        tenancy()->initialize($this->tenant);
        $admin = $this->createUser('admin@test.local', 'admin');
        $employee = $this->createUser('employee@test.local', 'employee');
        $inactiveAdmin = $this->createUser('inactive-admin@test.local', 'admin');
        $inactiveAdmin->update(['is_active' => false]);

        Notification::fake();
        app(NotificationDeliveryService::class)->sendToAdmins(
            new SystemNotification('عنوان', 'رسالة')
        );
        Notification::assertSentTo($admin, SystemNotification::class);
        Notification::assertNotSentTo($employee, SystemNotification::class);
        Notification::assertNotSentTo($inactiveAdmin, SystemNotification::class);

        $importantId = $this->insertNotification($admin, SystemNotification::class, 'تنبيه يحتاج متابعة');
        $this->insertNotification($admin, PaymentReceivedNotification::class, 'دفعة روتينية');
        tenancy()->end();

        $response = $this->actingAs($admin)
            ->withSession(['success' => 'رسالة نجاح موحدة'])
            ->get("http://{$this->tenantId}.localhost/notifications");

        $response->assertOk();
        $response->assertSee('تنبيه يحتاج متابعة');
        $response->assertDontSee('دفعة روتينية');
        $response->assertSee('aria-label="1 إشعار غير مقروء"', false);
        $response->assertSee(route('notifications.open', $importantId), false);
        $this->assertSame(1, substr_count($response->getContent(), 'رسالة نجاح موحدة'));
    }

    public function test_user_can_read_owned_notification_but_not_another_users_notification(): void
    {
        tenancy()->initialize($this->tenant);
        $admin = $this->createUser('first-admin@test.local', 'admin');
        $otherAdmin = $this->createUser('second-admin@test.local', 'admin');
        $ownedId = $this->insertNotification($admin, SystemNotification::class, 'إشعار المستخدم');
        $otherId = $this->insertNotification($otherAdmin, SystemNotification::class, 'إشعار مستخدم آخر');
        tenancy()->end();

        $this->actingAs($admin)
            ->post("http://{$this->tenantId}.localhost/notifications/{$ownedId}/read")
            ->assertRedirect();

        $this->actingAs($admin)
            ->post("http://{$this->tenantId}.localhost/notifications/{$otherId}/read")
            ->assertNotFound();

        tenancy()->initialize($this->tenant);
        $this->assertNotNull(DB::table('notifications')->where('id', $ownedId)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $otherId)->value('read_at'));
        tenancy()->end();
    }

    public function test_read_all_marks_only_important_notifications(): void
    {
        tenancy()->initialize($this->tenant);
        $admin = $this->createUser('read-all@test.local', 'admin');
        $importantId = $this->insertNotification($admin, SystemNotification::class, 'مهم');
        $routineId = $this->insertNotification($admin, PaymentReceivedNotification::class, 'روتيني');
        tenancy()->end();

        $this->actingAs($admin)
            ->post("http://{$this->tenantId}.localhost/notifications/read-all")
            ->assertRedirect();

        tenancy()->initialize($this->tenant);
        $this->assertNotNull(DB::table('notifications')->where('id', $importantId)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $routineId)->value('read_at'));
        tenancy()->end();
    }

    private function createUser(string $email, string $role): User
    {
        return User::create([
            'name' => 'مستخدم اختبار',
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function insertNotification(User $user, string $type, string $title): string
    {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => $title,
                'message' => 'رسالة اختبار',
                'action_url' => route('notifications.index'),
                'icon' => 'bell',
                'type' => 'warning',
            ], JSON_UNESCAPED_UNICODE),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
