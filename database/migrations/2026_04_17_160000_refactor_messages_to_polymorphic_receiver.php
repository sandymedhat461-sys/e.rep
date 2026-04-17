<?php

use App\Models\Doctor;
use App\Models\MedicalRep;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('messages')) {
            return;
        }

        if (Schema::hasColumn('messages', 'receiver_id') && !Schema::hasColumn('messages', 'receiver_doctor_id')) {
            return;
        }

        Schema::create('messages_new', function (Blueprint $table) {
            $table->id();
            $table->string('sender_type');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->string('receiver_type');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['sender_type', 'sender_id']);
            $table->index(['receiver_type', 'receiver_id']);
        });

        foreach (DB::table('messages')->orderBy('id')->cursor() as $row) {
            $receiverId = null;
            $receiverType = null;

            if (!empty($row->receiver_id) && !empty($row->receiver_type)) {
                $receiverId = (int) $row->receiver_id;
                $receiverType = (string) $row->receiver_type;
            } elseif (!empty($row->receiver_doctor_id)) {
                $receiverId = (int) $row->receiver_doctor_id;
                $receiverType = Doctor::class;
            } elseif (!empty($row->receiver_rep_id)) {
                $receiverId = (int) $row->receiver_rep_id;
                $receiverType = MedicalRep::class;
            }

            if ($receiverId === null || $receiverType === null) {
                continue;
            }

            $body = $row->body ?? $row->content ?? '';
            $isRead = (bool) ($row->is_read ?? $row->read_status ?? false);

            DB::table('messages_new')->insert([
                'id' => $row->id,
                'sender_type' => $row->sender_type,
                'sender_id' => $row->sender_id,
                'receiver_id' => $receiverId,
                'receiver_type' => $receiverType,
                'body' => $body,
                'is_read' => $isRead,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('messages');
        Schema::rename('messages_new', 'messages');
    }

    public function down(): void
    {
        if (!Schema::hasTable('messages')) {
            return;
        }

        if (Schema::hasColumn('messages', 'receiver_doctor_id')) {
            return;
        }

        Schema::create('messages_old', function (Blueprint $table) {
            $table->id();
            $table->string('sender_type');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_doctor_id')->nullable();
            $table->unsignedBigInteger('receiver_rep_id')->nullable();
            $table->text('content');
            $table->boolean('read_status')->default(false);
            $table->timestamps();

            $table->foreign('receiver_doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('receiver_rep_id')->references('id')->on('medical_reps')->onDelete('cascade');
            $table->index(['sender_type', 'sender_id']);
        });

        foreach (DB::table('messages')->orderBy('id')->cursor() as $row) {
            $doctorId = null;
            $repId = null;
            if ($row->receiver_type === Doctor::class) {
                $doctorId = $row->receiver_id;
            } elseif ($row->receiver_type === MedicalRep::class) {
                $repId = $row->receiver_id;
            } else {
                continue;
            }

            DB::table('messages_old')->insert([
                'id' => $row->id,
                'sender_type' => $row->sender_type,
                'sender_id' => $row->sender_id,
                'receiver_doctor_id' => $doctorId,
                'receiver_rep_id' => $repId,
                'content' => $row->body,
                'read_status' => (bool) $row->is_read,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('messages');
        Schema::rename('messages_old', 'messages');

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_receiver_check CHECK (receiver_doctor_id IS NOT NULL OR receiver_rep_id IS NOT NULL)');
            } catch (\Throwable) {
                //
            }
        }
    }
};
