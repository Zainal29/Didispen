(function () {
    function wrapText(ctx, text, maxWidth) {
        const lines = [];
        let line = '';

        String(text).trim().split(/\s+/).forEach(function (word) {
            if (ctx.measureText(word).width > maxWidth) {
                if (line) {
                    lines.push(line);
                    line = '';
                }

                let part = '';
                Array.from(word).forEach(function (character) {
                    if (part && ctx.measureText(part + character).width > maxWidth) {
                        lines.push(part);
                        part = character;
                    } else {
                        part += character;
                    }
                });
                line = part;
                return;
            }

            const candidate = line ? `${line} ${word}` : word;
            if (ctx.measureText(candidate).width > maxWidth && line) {
                lines.push(line);
                line = word;
            } else {
                line = candidate;
            }
        });

        if (line) lines.push(line);
        return lines;
    }

    window.drawDispensasiWatermark = function (ctx, canvas, rows) {
        const paddingX = Math.max(10, Math.round(canvas.width * 0.025));
        const maxWidth = Math.max(1, canvas.width - paddingX * 2);
        const baseFontSize = Math.max(
            10,
            Math.min(36, Math.round(Math.min(canvas.width * 0.045, canvas.height * 0.035)))
        );
        const paddingY = Math.max(8, Math.round(baseFontSize * 0.4));
        let scale = 1;
        let lines;
        let barHeight;
        const maxBarHeight = Math.round(canvas.height * 0.4);

        do {
            lines = [];
            rows.forEach(function (text, index) {
                const fontSize = Math.max(
                    8,
                    Math.round(baseFontSize * (index === 0 ? 0.78 : index === 1 ? 0.95 : 0.68) * scale)
                );
                const weight = index < 2 ? 'bold' : '500';
                ctx.font = `${weight} ${fontSize}px system-ui, -apple-system, sans-serif`;
                wrapText(ctx, text, maxWidth).forEach(function (line) {
                    lines.push({
                        text: line,
                        fontSize: fontSize,
                        weight: weight,
                        color: index < 2 ? '#ffffff' : '#cbd5e1',
                        lineHeight: Math.ceil(fontSize * 1.25),
                    });
                });
            });

            barHeight = Math.max(
                44,
                paddingY * 2 + lines.reduce(function (height, line) {
                    return height + line.lineHeight;
                }, 0)
            );

            if (barHeight <= maxBarHeight || scale <= 0.55) break;
            scale *= 0.9;
        } while (true);

        const barY = canvas.height - barHeight;
        const gradient = ctx.createLinearGradient(0, barY, 0, canvas.height);
        gradient.addColorStop(0, 'rgba(15, 23, 42, 0.65)');
        gradient.addColorStop(1, 'rgba(15, 23, 42, 0.95)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, barY, canvas.width, barHeight);
        ctx.fillStyle = '#10b981';
        ctx.fillRect(0, barY, canvas.width, 3);

        let y = barY + paddingY;
        lines.forEach(function (line) {
            ctx.font = `${line.weight} ${line.fontSize}px system-ui, -apple-system, sans-serif`;
            ctx.fillStyle = line.color;
            y += line.fontSize;
            ctx.fillText(line.text, paddingX, y, maxWidth);
            y += line.lineHeight - line.fontSize;
        });
    };
})();
