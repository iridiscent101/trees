<?php

namespace App\Console\Commands;

use App\Actions\GenerateTreeQrCode;
use App\Enums\RoleName;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('trees:generate-qr-codes {--force : Regenerate QR images that already exist}')]
#[Description('Generate printable QR images for registered trees')]
class GenerateTreeQrCodes extends Command
{
    public function handle(GenerateTreeQrCode $generateTreeQrCode): int
    {
        $generator = User::role(RoleName::Administrator->value)->oldest()->first();

        if (! $generator) {
            $this->error('An administrator account is required to generate QR codes.');

            return self::FAILURE;
        }

        $trees = Tree::query()->whereNotNull('tree_code')->with('qrCode')->oldest('id')->get()
            ->filter(fn (Tree $tree): bool => $this->option('force')
                || ! $tree->qrCode?->storage_path
                || ! Storage::disk('local')->exists($tree->qrCode->storage_path));

        if ($trees->isEmpty()) {
            $this->info('All registered trees already have QR code images.');

            return self::SUCCESS;
        }

        $progress = $this->output->createProgressBar($trees->count());
        $failed = 0;

        foreach ($trees as $tree) {
            try {
                $generateTreeQrCode->handle($tree, $generator);
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);

        if ($failed > 0) {
            $this->error("{$failed} QR code image(s) could not be generated. Review the application log.");

            return self::FAILURE;
        }

        $this->info($trees->count().' QR code image(s) generated successfully.');

        return self::SUCCESS;
    }
}
