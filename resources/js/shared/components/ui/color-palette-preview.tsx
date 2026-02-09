import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog';
import { generateColorScale, getContrastTextColor, type TailwindShade } from '@/shared/lib/color-scale';
import * as React from 'react';

interface ColorPalettePreviewProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    primaryColor: string;
    secondaryColor: string;
}

const SHADES: TailwindShade[] = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

export function ColorPalettePreview({ open, onOpenChange, primaryColor, secondaryColor }: ColorPalettePreviewProps) {
    const primaryScale = React.useMemo(() => {
        try {
            return generateColorScale(primaryColor);
        } catch {
            return null;
        }
    }, [primaryColor]);

    const secondaryScale = React.useMemo(() => {
        try {
            return generateColorScale(secondaryColor);
        } catch {
            return null;
        }
    }, [secondaryColor]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-4xl">
                <DialogHeader>
                    <DialogTitle>Color Palette Preview</DialogTitle>
                    <DialogDescription>Preview how your brand colors will look across all shades</DialogDescription>
                </DialogHeader>

                <div className="space-y-6">
                    {/* Primary Color Scale */}
                    <div className="space-y-2">
                        <div className="flex items-center gap-2">
                            <div className="h-4 w-4 rounded" style={{ backgroundColor: primaryColor }} />
                            <h3 className="text-sm font-medium">Primary Color</h3>
                            <span className="text-xs text-muted-foreground">{primaryColor}</span>
                        </div>
                        {primaryScale && (
                            <div className="grid grid-cols-11 gap-1">
                                {SHADES.map((shade) => {
                                    const bgColor = primaryScale[shade];
                                    const textColor = getContrastTextColor(bgColor);
                                    return (
                                        <div key={shade} className="flex flex-col items-center gap-1">
                                            <div
                                                className="flex h-16 w-full items-center justify-center rounded-md text-sm font-medium"
                                                style={{
                                                    backgroundColor: bgColor,
                                                    color: textColor,
                                                }}
                                            >
                                                aA
                                            </div>
                                            <span className="text-xs text-muted-foreground">{shade}</span>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                    {/* Secondary Color Scale */}
                    <div className="space-y-2">
                        <div className="flex items-center gap-2">
                            <div className="h-4 w-4 rounded" style={{ backgroundColor: secondaryColor }} />
                            <h3 className="text-sm font-medium">Secondary Color</h3>
                            <span className="text-xs text-muted-foreground">{secondaryColor}</span>
                        </div>
                        {secondaryScale && (
                            <div className="grid grid-cols-11 gap-1">
                                {SHADES.map((shade) => {
                                    const bgColor = secondaryScale[shade];
                                    const textColor = getContrastTextColor(bgColor);
                                    return (
                                        <div key={shade} className="flex flex-col items-center gap-1">
                                            <div
                                                className="flex h-16 w-full items-center justify-center rounded-md text-sm font-medium"
                                                style={{
                                                    backgroundColor: bgColor,
                                                    color: textColor,
                                                }}
                                            >
                                                aA
                                            </div>
                                            <span className="text-xs text-muted-foreground">{shade}</span>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
