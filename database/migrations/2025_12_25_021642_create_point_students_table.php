<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_students', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('point_id');
            $table->integer('student_id');
            $table->integer('type')->comment('0 = Lên xe, 1 = Xuống xe');
            $table->integer('status')->default(0)->comment('0 = Chưa điểm danh, 1 = Đã điểm danh');
            $table->integer('method')->comment('0 = Thủ công,1 = QR');
            $table->string('note')->nullable();
            $table->string('image_url')->nullable()->comment('Ảnh minh chứng');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('point_id')->references('id')->on('points')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            // Indexes
            $table->index('trip_id');
            $table->index('point_id');
            $table->index('student_id');
            $table->index('status');
            
            // Composite indexes để tối ưu các query patterns phổ biến:
            // 1. Query học sinh tại một điểm (WHERE point_id = X)
            // 2. Query học sinh tại một điểm trong một chuyến (WHERE trip_id = X AND point_id = Y)
            // 3. Query học sinh tại một điểm với status (WHERE point_id = X AND status = Y)
            // 4. Query học sinh tại một điểm trong một chuyến với status (WHERE trip_id = X AND point_id = Y AND status = Z)
            $table->index(['point_id', 'status']); // Tối ưu: WHERE point_id = X AND status = Y
            $table->index(['trip_id', 'point_id']); // Tối ưu: WHERE trip_id = X AND point_id = Y
            $table->index(['trip_id', 'point_id', 'status']); // Tối ưu: WHERE trip_id = X AND point_id = Y AND status = Z
            
            // Unique constraint: trong cùng một chuyến, một học sinh chỉ có thể xuất hiện một lần tại một điểm
            // Nhưng học sinh đó có thể xuất hiện tại cùng điểm đó trong các chuyến khác (ví dụ: lượt đi và lượt về)
            // Unique constraint này cũng tự động tạo index cho (trip_id, point_id, student_id)
            $table->unique(['trip_id', 'point_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_students');
    }
};
