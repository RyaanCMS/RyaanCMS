<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createOrganizationMemories();
        $this->createDecisionMemories();
        $this->createMeetingMemories();
        $this->createProjectMemories();
        $this->createCustomerMemories();
        $this->createProcessMemories();
    }

    public function down(): void
    {
        Schema::dropIfExists('process_memories');
        Schema::dropIfExists('customer_memories');
        Schema::dropIfExists('project_memories');
        Schema::dropIfExists('meeting_memories');
        Schema::dropIfExists('decision_memories');
        Schema::dropIfExists('organization_memories');
    }

    private function createOrganizationMemories(): void
    {
        Schema::create('organization_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('domain', 80)->default('general')->index();
            $table->string('memory_type', 80)->default('company_context')->index();
            $table->string('title', 255);
            $table->text('summary')->nullable();
            $table->json('facts')->nullable();
            $table->json('metrics')->nullable();
            $table->json('tags')->nullable();
            $table->decimal('confidence', 4, 2)->default(0.70);
            $table->timestamp('observed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['domain', 'memory_type']);
        });
    }

    private function createDecisionMemories(): void
    {
        Schema::create('decision_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('domain', 80)->default('general')->index();
            $table->string('decision', 500);
            $table->text('rationale')->nullable();
            $table->json('alternatives')->nullable();
            $table->json('expected_impact')->nullable();
            $table->json('actual_outcome')->nullable();
            $table->decimal('confidence', 4, 2)->default(0.70);
            $table->boolean('is_rule_candidate')->default(false)->index();
            $table->timestamp('decided_at')->nullable()->index();
            $table->timestamps();

            $table->index(['domain', 'is_rule_candidate']);
        });
    }

    private function createMeetingMemories(): void
    {
        Schema::create('meeting_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 255);
            $table->json('attendees')->nullable();
            $table->text('summary')->nullable();
            $table->json('decisions')->nullable();
            $table->json('action_items')->nullable();
            $table->json('blockers')->nullable();
            $table->timestamp('meeting_at')->nullable()->index();
            $table->timestamps();
        });
    }

    private function createProjectMemories(): void
    {
        Schema::create('project_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('domain', 80)->default('general')->index();
            $table->string('project_name', 255);
            $table->text('goal')->nullable();
            $table->json('milestones')->nullable();
            $table->json('risks')->nullable();
            $table->json('lessons')->nullable();
            $table->json('outcomes')->nullable();
            $table->string('status', 40)->default('active')->index();
            $table->timestamps();
        });
    }

    private function createCustomerMemories(): void
    {
        Schema::create('customer_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('segment', 120)->nullable()->index();
            $table->string('customer_key', 160)->nullable()->index();
            $table->json('feedback')->nullable();
            $table->json('objections')->nullable();
            $table->json('churn_reasons')->nullable();
            $table->json('value_moments')->nullable();
            $table->json('support_themes')->nullable();
            $table->decimal('health_score', 5, 2)->nullable()->index();
            $table->timestamp('observed_at')->nullable()->index();
            $table->timestamps();
        });
    }

    private function createProcessMemories(): void
    {
        Schema::create('process_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('domain', 80)->default('general')->index();
            $table->string('process_name', 255);
            $table->json('states')->nullable();
            $table->json('owners')->nullable();
            $table->json('sla_rules')->nullable();
            $table->json('exceptions')->nullable();
            $table->json('controls')->nullable();
            $table->json('improvement_history')->nullable();
            $table->timestamps();

            $table->index(['domain', 'process_name']);
        });
    }
};
