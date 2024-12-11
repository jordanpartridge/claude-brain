<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Entities table - Base table for all nodes in our knowledge graph
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);  // user, project, concept, etc.
            $table->string('name', 255);
            $table->json('metadata')->nullable();  // Flexible storage for entity-specific data
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'name']);
            $table->index('created_at');
        });

        // Relationship types table - Defines valid relationship types and their rules
        Schema::create('relationship_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('inverse_name')->nullable();
            $table->boolean('is_bidirectional')->default(false);
            $table->json('allowed_entity_types')->nullable();
            $table->json('required_metadata_fields')->nullable();
            $table->json('validation_rules')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Relationships table - Edges in our knowledge graph
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('to_entity_id')->constrained('entities')->onDelete('cascade');
            $table->string('type', 50);  // references relationship_types.name
            $table->foreignId('inverse_of')->nullable()->constrained('relationships')->onDelete('cascade');
            $table->json('metadata')->nullable();  // Additional relationship data
            $table->timestamp('started_at')->nullable();  // When the relationship began
            $table->timestamp('ended_at')->nullable();    // Optional end time
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['from_entity_id', 'type']);
            $table->index(['to_entity_id', 'type']);
            $table->index('started_at');
            $table->foreign('type')->references('name')->on('relationship_types');
        });

        // Observations table - Facts and properties about entities
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained()->onDelete('cascade');
            $table->text('content');  // The actual observation
            $table->string('source', 100)->nullable();  // Where this observation came from
            $table->decimal('confidence', 5, 4)->nullable();  // How sure we are (0-1)
            $table->json('metadata')->nullable();  // Additional context
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('entity_id');
            $table->index('created_at');
            $table->index('confidence');
        });

        // Interactions table - Conversation/session history
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id');  // Unique session identifier
            $table->foreignId('user_entity_id')->constrained('entities')->onDelete('cascade');
            $table->string('type', 50);  // Type of interaction (chat, command, etc.)
            $table->json('context');     // Full interaction context
            $table->json('metadata')->nullable();  // Additional interaction data
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['session_id', 'user_entity_id']);
            $table->index('created_at');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interactions');
        Schema::dropIfExists('observations');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('relationship_types');
        Schema::dropIfExists('entities');
    }
};