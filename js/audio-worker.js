// audio-worker.js
let audioContext;
let audioBufferCache = {};

self.onmessage = async (e) => {
    const { type, filename } = e.data;

    if (type === 'init') {
        audioContext = new (self.AudioContext || self.webkitAudioContext)();
    }

    if (type === 'play' && audioContext) {
        try {
            if (!audioBufferCache[filename]) {
                const response = await fetch(`audio/ar/${filename}.mp3`);
                if (!response.ok) {
                    throw new Error(`Failed to load ${filename}.mp3`);
                }
                const arrayBuffer = await response.arrayBuffer();
                audioBufferCache[filename] = await audioContext.decodeAudioData(arrayBuffer);
            }

            const source = audioContext.createBufferSource();
            source.buffer = audioBufferCache[filename];
            source.connect(audioContext.destination);
            source.start(0);

            source.onended = () => {
                self.postMessage({ type: 'ended' });
            };

        } catch (error) {
            console.error(`Error in audio worker for ${filename}:`, error);
            self.postMessage({ type: 'ended' });
        }
    }
};