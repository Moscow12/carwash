<?php

namespace Tests\Feature\Owner;

use App\Livewire\Owner\Settings\Setup;
use App\Models\Business;
use App\Models\CarwashSetting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class CurrencySettingsTest extends TestCase
{
    private User $owner;

    private Business $business;

    /**
     * The project migrations use MySQL-only statements, so the tables are built on SQLite here.
     * carwash_settings gets one loose column per fillable attribute.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('customer');
            $table->uuid('role_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('status')->default('active');
            $table->uuid('owner_id')->nullable();
            $table->timestamps();
        });

        Schema::create('carwash_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            foreach ((new CarwashSetting)->getFillable() as $column) {
                $table->text($column)->nullable();
            }
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('status')->default('active');
            $table->uuid('business_id');
            $table->timestamps();
        });

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->business = Business::create(['name' => 'TS Shop', 'status' => 'active', 'owner_id' => $this->owner->id]);
    }

    public function test_currency_fields_show_readable_labels_not_raw_values(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->assertSeeHtml('<option value="TZS" wire:key="currency-TZS">TZS – Tanzanian Shilling</option>')
            ->assertSee('Before the amount')
            ->assertSee(', Comma (1,000,000)')
            ->assertSee('. Period (10.50)')
            ->assertSee('2 (1,000.50)')
            ->assertDontSeeHtml('<optgroup');
    }

    public function test_choosing_a_currency_selects_its_symbol(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->set('currency', 'KES')
            ->assertSet('currency_symbol', 'KSh')
            ->set('currency', 'USD')
            ->assertSet('currency_symbol', '$')
            ->set('currency_symbol', 'USD')
            ->set('currency', 'USD')
            ->assertSet('currency_symbol', 'USD');
    }

    public function test_preview_follows_the_choices(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->set('currency', 'TZS')
            ->set('currency_symbol', 'TSh')
            ->set('currency_position', 'before')
            ->set('decimal_places', 2)
            ->set('thousand_separator', ',')
            ->set('decimal_separator', '.')
            ->assertSee('TSh 1,234,567.89')
            ->set('currency_position', 'after')
            ->set('decimal_places', 0)
            ->set('thousand_separator', ' ')
            ->assertSee('1 234 568 TSh');
    }

    public function test_valid_choices_are_saved_exactly(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->set('currency', 'EUR')
            ->set('currency_symbol', '€')
            ->set('currency_position', 'after')
            ->set('decimal_places', 2)
            ->set('thousand_separator', '.')
            ->set('decimal_separator', ',')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $settings = CarwashSetting::where('business_id', $this->business->id)->firstOrFail();

        $this->assertSame(['EUR', '€', 'after', 2, '.', ','], [
            $settings->currency, $settings->currency_symbol, $settings->currency_position,
            (int) $settings->decimal_places, $settings->thousand_separator, $settings->decimal_separator,
        ]);
    }

    public function test_no_thousand_separator_is_saved_as_empty_string(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->set('thousand_separator', '')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertSame('', CarwashSetting::where('business_id', $this->business->id)->value('thousand_separator'));
    }

    public function test_invalid_or_conflicting_values_are_rejected(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->set('currency', 'XXX')
            ->set('currency_position', 'middle')
            ->set('decimal_places', 7)
            ->set('thousand_separator', '.')
            ->set('decimal_separator', '.')
            ->call('saveSettings')
            ->assertHasErrors(['currency', 'currency_position', 'decimal_places', 'decimal_separator']);
    }

    public function test_symbol_must_match_the_currency(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->set('currency', 'TZS')
            ->set('currency_symbol', '£')
            ->call('saveSettings')
            ->assertHasErrors('currency_symbol');
    }

    public function test_previously_saved_custom_symbol_stays_selectable(): void
    {
        CarwashSetting::getForBusiness($this->business->id)->update(['currency' => 'TZS', 'currency_symbol' => 'Tsh.']);

        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->assertSet('currency_symbol', 'Tsh.')
            ->assertSee('Tsh. (currently saved)')
            ->call('saveSettings')
            ->assertHasNoErrors('currency_symbol');
    }

    public function test_broken_saved_currency_falls_back_to_tzs(): void
    {
        CarwashSetting::getForBusiness($this->business->id)->update(['currency' => 'name', 'currency_symbol' => 'TZS']);

        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->assertSet('currency', 'TZS')
            ->assertSet('currency_symbol', 'TZS')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertSame('TZS', CarwashSetting::where('business_id', $this->business->id)->value('currency'));
    }

    public function test_system_settings_show_readable_choices(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-24 14:30:00', 'Africa/Dar_es_Salaam'));

        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->call('setTab', 'system')
            ->assertSee('24/10/2026 — Day/Month/Year')
            ->assertSee('Oct 24, 2026 — Mon Day, Year')
            ->assertSee('14:30 — 24-hour')
            ->assertSee('02:30 PM — 12-hour')
            ->assertSee('Dar es Salaam (UTC+03:00)')
            ->assertSeeHtml('<optgroup label="East Africa"')
            ->assertSee('Kiswahili')
            ->assertDontSee('Select Provider')
            ->assertDontSeeHtml("'id' =>");
    }

    public function test_date_time_preview_follows_format_and_timezone(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-24 11:30:00', 'UTC'));

        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->call('setTab', 'system')
            ->set('date_format', 'd M Y')
            ->set('time_format', 'h:i A')
            ->set('timezone', 'Africa/Dar_es_Salaam')
            ->assertSee('24 Oct 2026 02:30 PM')
            ->set('timezone', 'UTC')
            ->set('time_format', 'H:i')
            ->assertSee('24 Oct 2026 11:30');
    }

    public function test_system_settings_are_saved_exactly(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->set('date_format', 'd/m/Y')
            ->set('time_format', 'h:i A')
            ->set('timezone', 'Africa/Nairobi')
            ->set('language', 'sw')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $settings = CarwashSetting::where('business_id', $this->business->id)->firstOrFail();

        $this->assertSame(['d/m/Y', 'h:i A', 'Africa/Nairobi', 'sw'], [$settings->date_format, $settings->time_format, $settings->timezone, $settings->language]);
    }

    public function test_invalid_system_settings_are_rejected(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->set('date_format', 'id')
            ->set('time_format', 'U')
            ->set('timezone', 'Mars/Olympus')
            ->set('language', 'name')
            ->call('saveSettings')
            ->assertHasErrors(['date_format', 'time_format', 'timezone', 'language']);
    }

    public function test_broken_saved_system_settings_fall_back_to_defaults(): void
    {
        CarwashSetting::getForBusiness($this->business->id)->update(['date_format' => 'id', 'time_format' => 'id', 'timezone' => 'id', 'language' => 'name']);

        Livewire::actingAs($this->owner)
            ->test(Setup::class)
            ->assertSet('date_format', 'Y-m-d')
            ->assertSet('time_format', 'H:i')
            ->assertSet('timezone', 'Africa/Dar_es_Salaam')
            ->assertSet('language', 'en')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertSame('Africa/Dar_es_Salaam', CarwashSetting::where('business_id', $this->business->id)->value('timezone'));
    }
}
