'use client';

import * as React from 'react';
import { HexColorPicker } from 'react-colorful';

import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/shared/components/ui/popover';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/shared/components/ui/tabs';
import { cn } from '@/shared/lib/utils';

interface ColorPickerProps {
    color?: string;
    onChange?: (value: string) => void;
}

const predefinedColors = ['#000000', '#ffffff', '#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6', '#6366f1', '#a855f7', '#ec4899'];

export function ColorPicker({ color = '#000000', onChange }: ColorPickerProps) {
    const [currentColor, setCurrentColor] = React.useState(color);

    const handleColorChange = (newColor: string) => {
        setCurrentColor(newColor);
        onChange?.(newColor);
    };

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button variant="outline" className="w-full justify-start text-left font-normal">
                    <div className="flex w-full items-center gap-2">
                        <div className="h-4 w-4 rounded border bg-cover! bg-center! transition-all" style={{ backgroundColor: currentColor }} />
                        <div className="flex-1 truncate">{currentColor}</div>
                    </div>
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-64 p-3">
                <Tabs defaultValue="solid" className="w-full">
                    <TabsList className="mb-3 w-full">
                        <TabsTrigger className="flex-1" value="solid">
                            Solid
                        </TabsTrigger>
                        <TabsTrigger className="flex-1" value="pick">
                            Pick
                        </TabsTrigger>
                    </TabsList>
                    <TabsContent value="solid" className="mt-0">
                        <div className="grid grid-cols-5 gap-2">
                            {predefinedColors.map((presetColor) => (
                                <div
                                    key={presetColor}
                                    style={{ backgroundColor: presetColor }}
                                    className={cn(
                                        'h-8 w-8 cursor-pointer rounded-md border',
                                        'ring-offset-background transition-all hover:scale-105',
                                        'active:scale-100',
                                        currentColor === presetColor && 'ring-2 ring-ring ring-offset-2',
                                    )}
                                    onClick={() => handleColorChange(presetColor)}
                                />
                            ))}
                        </div>
                    </TabsContent>
                    <TabsContent value="pick" className="mt-0 space-y-3">
                        <HexColorPicker color={currentColor} onChange={handleColorChange} className="mx-auto w-full" />
                        <Input placeholder="#000000" value={currentColor} onChange={(e) => handleColorChange(e.target.value)} className="h-9" />
                    </TabsContent>
                </Tabs>
            </PopoverContent>
        </Popover>
    );
}
