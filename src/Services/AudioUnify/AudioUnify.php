<?php

namespace App\Services\AudioUnify;

use FFMpeg\FFMpeg;
use Symfony\Component\HttpKernel\KernelInterface;

class AudioUnify
{
    private FFMpeg $ffmpeg;

    public function __construct(
        private string $ffmpegBinaries,
        private string $ffprobeBinaries,
        private KernelInterface $kernelInterface,
    ) {
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => $ffmpegBinaries,
            'ffprobe.binaries'  => $ffprobeBinaries,
            'timeout'          => 3600, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
        ]);
    }

    public function unifier(array $audios): ?string
    {
        $audioOutput = $this->ffmpeg->open($audios[0]);

        $newFileName = $this->kernelInterface->getProjectDir() . '/var/mp3/unifieds/'.(new \DateTime())->format('d_m_y_h_i_s').'.mp3';

        try {
            $audioOutput
                ->concat($audios)
                ->saveFromSameCodecs($newFileName, TRUE)    
            ;
            return $newFileName;
        } catch (\Throwable $th) {
            throw $th;
        }

        return null;
    }
}
