<?php
declare( strict_types=1 );

require 'tool.inc';

function smpl_loop( string $fn, string &$wav ) : void
{
	// fmt  chunk - get sample rate , encoder delay
	// smpl chunk - get loop start , loop end
	$pos = 0xc;
	$len = strlen($wav);

	$sample_rate   = -1;
	$encoder_delay = 0;
	$loop_start = -1;
	$loop_end   = -1;
	while ( $pos < $len )
	{
		$mgc = tool::substr($wav, $pos + 0, 4);
		$siz = tool::ordstr($wav, $pos + 4, 4);
			$siz += 8;

		tool::trace($fn, $mgc, $siz);
		switch ( $mgc )
		{
			case 'fmt ':
				// typedef struct {
				//   00  char        chunkID[4];         // Must contain "fmt " (0x666D7420)
				//   04  uint32_t    chunkSize;          // 16 for PCM. If > 16, extension data exists.
				//   08  uint16_t    audioFormat;        // PCM = 1 (Linear). 3 = IEEE Float. 65534 = Extensible.
				//   0a  uint16_t    numChannels;        // Mono = 1, Stereo = 2, etc.
				//   0c  uint32_t    sampleRate;         // e.g., 44100, 48000
				//   10  uint32_t    byteRate;           // sampleRate * numChannels * (bitsPerSample / 8)
				//   14  uint16_t    blockAlign;         // numChannels * (bitsPerSample / 8)
				//   16  uint16_t    bitsPerSample;      // 8, 16, 24, or 32 bits
				//
				//   /* Optional expansion fields present if chunkSize > 16 (Non-PCM or Extensible) */
				//   // uint16_t cbSize;             // Size of the extension (0 or greater)
				//   // uint8_t  extensionData[cbSize];
				// } FormatChunk;

				$sample_rate = tool::ordstr($wav, $pos + 0x0c, 4);
				// Read Sony Extension Encoder Delay if available
				//if ( $siz >= 0x28 )
					//$encoder_delay = tool::ordstr($wav, $pos + 0x26, 2);
				$pos += $siz;
				break;

			case 'smpl':
				// typedef struct {
				//   00  uint32_t    dwIdentifier;       // Unique loop block ID
				//   04  uint32_t    dwType;             // Loop direction: 0=Forward, 1=Alternating, 2=Backward
				//   08  uint32_t    dwStart;            // Offset start position within the data payload (in samples)
				//   0c  uint32_t    dwEnd;              // Offset end position within the data payload (in samples)
				//   10  uint32_t    dwFraction;         // Fine-tuning fraction between samples (0 to 0xFFFFFFFF)
				//   14  uint32_t    dwPlayCount;        // Target repeat count (0 = loop indefinitely)
				// } SampleLoop;
				//
				// typedef struct {
				//   00  char        chunkID[4];         // Must contain "smpl" (0x736D706C)
				//   04  uint32_t    chunkSize;          // Size of chunk payload (36 + (numSampleLoops * 24))
				//   08  uint32_t    dwManufacturer;     // MIDI Manufacturer ID
				//   0c  uint32_t    dwProduct;          // MIDI Product ID
				//   10  uint32_t    dwSamplePeriod;     // Sample period in nanoseconds (e.g., 22675 for 44.1kHz)
				//   14  uint32_t    dwMIDIUnityNote;    // Root MIDI pitch value (0-127, e.g., 60 for Middle C)
				//   18  uint32_t    dwMIDIPitchFraction;// Fine-tuning fraction of a semitone
				//   1c  uint32_t    dwSMPTEFormat;      // SMPTE rate format: 0=None, 24, 25, 29, 30
				//   20  uint32_t    dwSMPTEOffset;      // Time code alignment execution offset
				//   24  uint32_t    cSampleLoops;       // Execution target array size for loops
				//   28  uint32_t    cbSamplerData;      // Size of optional sampler extension payload (usually 0)
				//
				//   // SampleLoop loops[cSampleLoops]; // Inline variable array structure mapping
				// } SamplerChunk;
				$loop_cnt = tool::ordstr($wav, $pos + 0x24, 4);
				if ( $loop_cnt > 0 )
				{
					$loop_start = tool::ordstr($wav, $pos + 0x2c + 0x08, 4);
					$loop_end   = tool::ordstr($wav, $pos + 0x2c + 0x0c, 4);
						$loop_start /= $sample_rate;
						$loop_end   /= $sample_rate;
				}
				$pos += $siz;
				break;

			case 'fact':
				// typedef struct {
				//   00  char        chunkID[4];         // Must contain "fact" (0x66616374)
				//   04  uint32_t    chunkSize;          // Typically 4 bytes
				//   08  uint32_t    dwSampleLength;     // Total number of sample frames per channel
				// } FactChunk;
				$pos += $siz;
				break;

			case 'data':
				// typedef struct {
				//   00  char        chunkID[4];     // Must contain "data" (0x64617461)
				//   04  uint32_t    chunkSize;      // Total byte length of audio stream data array
				//   // uint8_t   audioData[];   // Interleaved sample stream (Length = chunkSize)
				// } DataChunk;
				$pos += $siz;
				break;

			default: // trailing zeroes = SKIP
				$pos = $len;
				break;
		} // switch ( $mgc )
	} // while ( $pos < $len )

	if ( $sample_rate < 0 )  return;
	if ( $loop_start  < 0 )  return;
	if ( $loop_end    < 0 )  return;

	$txt  = sprintf("LOOP_START=%.6f\r\n", $loop_start);
	$txt .= sprintf("LOOP_END=%.6f\r\n"  , $loop_end  );
	tool::save($fn.'.loop', $txt);
}

function ripwave( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$wav = [];
	$pos = 0;
	while (1)
	{
		$pos = strpos($file, 'RIFF', $pos);
		if ( $pos === false )
			break;

		$mgc = substr($file, $pos + 8, 8);
		if ( $mgc !== 'WAVEfmt ' )
		{
			$pos += 4;
			continue;
		}

		$size = tool::ordstr($file, $pos + 4, 4);
		tool::trace('found wave', $pos, $size);

		$wav[] = tool::substr($file, $pos, $size);
		$pos  += $size;
	} // while (1)

	if ( ! isset($wav[0]) ) // empty
		return;
	if ( ! isset($wav[1]) ) // likely it is a WAV file
	{
		smpl_loop($fname, $wav[0]);
		return;
	}

	// multiple WAV
	$dir = str_replace('.', '_', $fname);
	foreach ( $wav as $wk => $wv )
	{
		$fn = sprintf('%s/%04d.wav', $dir, $wk);
		smpl_loop($fn, $wv);
		tool::save($fn, $wv);
	} // foreach ( $wav as $wk => $wv )
}

tool::argv_callback($argv, 'ripwave');

/*
#!/bin/bash
# Usage: ./play-bgm.sh song.ogg
FILE="$1"
LOOP_FILE="${FILE}.loop"
if [ -z "$FILE" ]; then
    echo "Usage: $0 <music_file.ogg>"
    exit 1
fi
# Check if the text descriptor file generated by your C app exists
if [ -f "$LOOP_FILE" ]; then
    echo "Found loop descriptor track metadata!"

    # Source the text file variables directly into the Bash context
    source "$LOOP_FILE"

    # Force mpv to use an A-B repeat sequence:
    # --loop-file=inf keeps the process running forever
    # --ab-loop-a dictates where the jump reset returns to
    # --ab-loop-b specifies the physical edge boundary that triggers the reset
    echo "Looping seamlessly between ${START}s and ${END}s..."
    mpv --loop-file=inf --ab-loop-a="$START" --ab-loop-b="$END" "$FILE"
else
    echo "No loop file found. Playing track normally."
    mpv "$FILE"
fi
*/
