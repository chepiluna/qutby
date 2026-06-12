<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->string('kode_vendor')->unique();
                $table->string('nama_vendor');
                $table->text('alamat')->nullable();
                $table->string('no_telepon')->nullable();
                $table->string('email')->nullable();
                $table->decimal('diskon_persen', 5, 2)->default(0);
                $table->string('nama_bank')->nullable();
                $table->string('nomor_rekening')->nullable();
                $table->integer('periode_pembayaran')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pembelians')) {
            Schema::create('pembelians', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('nomor')->unique();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->decimal('total', 15, 2)->default(0);
                $table->decimal('diskon', 15, 2)->default(0);
                $table->boolean('ppn')->default(false);
                $table->decimal('total_akhir', 15, 2)->default(0);
                $table->string('status')->default('menunggu');
                $table->date('estimasi_datang')->nullable();
                $table->string('status_pengiriman')->nullable();
                $table->string('syarat_pembayaran')->default('tunai');
                $table->string('referensi_pr')->nullable();
                $table->text('catatan_vendor')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pembelian_details')) {
            Schema::create('pembelian_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pembelian_id')->constrained('pembelians')->cascadeOnDelete();
                $table->foreignId('barang_id')->nullable()->constrained('barang')->nullOnDelete();
                $table->integer('qty')->default(0);
                $table->string('satuan')->nullable();
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('hpp', 15, 2)->default(0);
                $table->decimal('diskon_persen', 5, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('po_termins')) {
            Schema::create('po_termins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pembelian_id')->constrained('pembelians')->cascadeOnDelete();
                $table->integer('termin_ke')->default(1);
                $table->date('due_date')->nullable();
                $table->decimal('nominal', 15, 2)->default(0);
                $table->string('status')->default('belum_lunas');
                $table->date('tanggal_bayar')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grns')) {
            Schema::create('grns', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_grn')->unique();
                $table->foreignId('pembelian_id')->nullable()->constrained('pembelians')->nullOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->date('tanggal_terima');
                $table->string('nomor_surat_jalan')->nullable();
                $table->string('gudang_tujuan')->nullable();
                $table->text('catatan')->nullable();
                $table->string('status')->default('draft');
                $table->string('status_penerimaan')->nullable();
                $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('dikonfirmasi_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grn_details')) {
            Schema::create('grn_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('grn_id')->constrained('grns')->cascadeOnDelete();
                $table->foreignId('pembelian_detail_id')->nullable()->constrained('pembelian_details')->nullOnDelete();
                $table->foreignId('barang_id')->nullable()->constrained('barang')->nullOnDelete();
                $table->integer('qty_po')->default(0);
                $table->integer('qty_diterima')->default(0);
                $table->integer('qty_rusak')->default(0);
                $table->string('kondisi')->default('baik');
                $table->string('foto')->nullable();
                $table->text('catatan_item')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('faktur_pembelians')) {
            Schema::create('faktur_pembelians', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal_faktur')->nullable();
                $table->date('tanggal_pembayaran')->nullable();
                $table->string('nomor_faktur_vendor')->nullable();
                $table->string('nomor_pembayaran_vendor')->nullable();
                $table->foreignId('pembelian_id')->nullable()->constrained('pembelians')->nullOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->decimal('total_bruto', 15, 2)->default(0);
                $table->decimal('diskon_persen', 5, 2)->default(0);
                $table->decimal('total_netto', 15, 2)->default(0);
                $table->string('bukti_pembayaran')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('faktur_pembelian_details')) {
            Schema::create('faktur_pembelian_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('faktur_pembelian_id')->constrained('faktur_pembelians')->cascadeOnDelete();
                $table->foreignId('barang_id')->nullable()->constrained('barang')->nullOnDelete();
                $table->integer('qty')->default(0);
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('kartu_stok')) {
            Schema::create('kartu_stok', function (Blueprint $table) {
                $table->id();
                $table->foreignId('barang_id')->nullable()->constrained('barang')->nullOnDelete();
                $table->date('tanggal')->nullable();
                $table->string('jenis')->nullable();
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('keterangan')->nullable();
                $table->boolean('is_saldo_awal')->nullable();
                $table->integer('masuk')->default(0);
                $table->integer('harga_masuk')->default(0);
                $table->integer('keluar')->default(0);
                $table->integer('harga_keluar')->default(0);
                $table->integer('pembelian_unit')->default(0);
                $table->integer('pembelian_harga_unit')->default(0);
                $table->integer('pembelian_total')->default(0);
                $table->integer('hpp_unit')->default(0);
                $table->integer('hpp_harga_unit')->default(0);
                $table->integer('hpp_total')->default(0);
                $table->integer('saldo_unit')->default(0);
                $table->integer('saldo_harga_unit')->default(0);
                $table->integer('saldo_total')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('kartu_stok_average')) {
            Schema::create('kartu_stok_average', function (Blueprint $table) {
                $table->id();
                $table->foreignId('barang_id')->nullable()->constrained('barang')->nullOnDelete();
                $table->date('tanggal')->nullable();
                $table->string('jenis')->nullable();
                $table->integer('qty')->default(0);
                $table->decimal('harga_beli', 15, 2)->default(0);
                $table->decimal('hpp_per_unit', 15, 2)->default(0);
                $table->decimal('hpp_total', 15, 2)->default(0);
                $table->integer('sisa_unit')->default(0);
                $table->decimal('harga_rata_rata', 15, 2)->default(0);
                $table->decimal('nilai_persediaan', 15, 2)->default(0);
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'kartu_stok_average',
            'kartu_stok',
            'faktur_pembelian_details',
            'faktur_pembelians',
            'grn_details',
            'grns',
            'po_termins',
            'pembelian_details',
            'pembelians',
            'vendors',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
