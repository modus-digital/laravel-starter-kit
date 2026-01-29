"use client"

import * as React from "react"
import { ChevronDownIcon } from "lucide-react"
import { format } from "date-fns"

import { Button } from "@/shared/components/ui/button"
import { Calendar } from "@/shared/components/ui/calendar"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/shared/components/ui/popover"
import { cn } from "@/shared/lib/utils"

export type DatePickerProps = {
  value?: Date | string | null
  onChange?: (date: Date | undefined) => void
  placeholder?: string
  id?: string
  className?: string
  disabled?: boolean
}

export function DatePicker({
  value,
  onChange,
  placeholder = "Select date",
  id,
  className,
  disabled = false,
}: DatePickerProps) {
  const [open, setOpen] = React.useState(false)

  // Convert value to Date if it's a string
  const dateValue = React.useMemo(() => {
    if (!value) return undefined
    if (value instanceof Date) return value
    if (typeof value === "string") {
      const parsed = new Date(value)
      return isNaN(parsed.getTime()) ? undefined : parsed
    }
    return undefined
  }, [value])

  const handleSelect = (selectedDate: Date | undefined) => {
    onChange?.(selectedDate)
    if (selectedDate) {
      setOpen(false)
    }
  }

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          id={id}
          disabled={disabled}
          className={cn("w-full justify-between font-normal", className)}
        >
          {dateValue ? format(dateValue, "PPP") : placeholder}
          <ChevronDownIcon className="ml-2 h-4 w-4 opacity-50" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto overflow-hidden p-0" align="start">
        <Calendar
          mode="single"
          selected={dateValue}
          captionLayout="dropdown"
          onSelect={handleSelect}
        />
      </PopoverContent>
    </Popover>
  )
}
