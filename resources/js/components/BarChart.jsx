import React, { useState } from 'react';
import { Text } from '@shopify/polaris';

/**
 * A minimal dependency-free bar chart. Built by hand rather than pulling
 * in a charting library — this app only ever needs one simple series, so
 * a full charting dependency isn't worth the bundle weight or the extra
 * approval to add it.
 */
export default function BarChart({ data, labelFormatter = (l) => l }) {
    const [hovered, setHovered] = useState(null);
    const max = Math.max(1, ...data.map((d) => d.total));

    return (
        <div>
            <div style={{ display: 'flex', alignItems: 'flex-end', gap: '4px', height: '160px', borderBottom: '1px solid #edeef1' }}>
                {data.map((point, i) => {
                    const heightPct = Math.max(3, (point.total / max) * 100);
                    const isHovered = hovered === i;

                    return (
                        <div
                            key={point.period}
                            onMouseEnter={() => setHovered(i)}
                            onMouseLeave={() => setHovered(null)}
                            style={{
                                flex: 1,
                                height: '100%',
                                display: 'flex',
                                alignItems: 'flex-end',
                                position: 'relative',
                                minWidth: 0,
                            }}
                        >
                            {isHovered && (
                                <div style={{
                                    position: 'absolute',
                                    bottom: `calc(${heightPct}% + 8px)`,
                                    left: '50%',
                                    transform: 'translateX(-50%)',
                                    background: '#1f2328',
                                    color: '#fff',
                                    fontSize: '11px',
                                    padding: '4px 8px',
                                    borderRadius: '6px',
                                    whiteSpace: 'nowrap',
                                    zIndex: 1,
                                }}>
                                    {labelFormatter(point.period)}: {point.total}
                                </div>
                            )}
                            <div style={{
                                width: '100%',
                                height: point.total > 0 ? `${heightPct}%` : '4px',
                                background: point.total > 0 ? (isHovered ? '#004c3f' : '#008060') : (isHovered ? '#d1d5db' : '#eef0f3'),
                                borderRadius: '3px 3px 0 0',
                                transition: 'background .1s ease',
                            }} />
                        </div>
                    );
                })}
            </div>
            <div style={{ display: 'flex', gap: '4px', marginTop: '8px' }}>
                {data.map((point, i) => (
                    <div key={point.period} style={{ flex: 1, textAlign: 'center', minWidth: 0 }}>
                        {(i === 0 || i === data.length - 1 || i === Math.floor(data.length / 2)) && (
                            <Text as="span" variant="bodyXs" tone="subdued" truncate>
                                {labelFormatter(point.period)}
                            </Text>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
