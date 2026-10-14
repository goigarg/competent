<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.portal')]
#[Title('Republik Österreich - Beamtinnen- & Beamten-Portal (Bürgerservice 2030)')]
class EmployeePortal extends Component
{
    public ?int $selectedConversationId = null;

    public string $statusFilter = 'all'; // 'all', 'pending_officer', 'in_review', 'resolved'

    public string $searchQuery = '';

    public string $officerNoteInput = '';

    public string $assignedOfficerInput = '';

    public string $officerResponseInput = '';

    public ?string $feedbackMessage = null;

    public function mount(?int $conversationId = null): void
    {
        if ($conversationId) {
            $this->selectConversation($conversationId);
        } else {
            $latest = Conversation::escalated()->latest('escalated_at')->first();
            if ($latest) {
                $this->selectConversation($latest->id);
            }
        }
    }

    public function selectConversation(int $id): void
    {
        $this->selectedConversationId = $id;
        $this->feedbackMessage = null;
        $conv = Conversation::find($id);

        if ($conv) {
            $this->officerNoteInput = $conv->officer_notes ?? '';
            $this->assignedOfficerInput = $conv->assigned_officer ?? '';

            // If it's still pending, auto-mark it as in review when an officer inspects it
            if ($conv->status === 'pending_officer') {
                $conv->update(['status' => 'in_review']);
            }
        }
    }

    public function updateStatus(string $newStatus): void
    {
        if (! in_array($newStatus, ['pending_officer', 'in_review', 'resolved'])) {
            return;
        }

        if ($this->selectedConversationId) {
            $conv = Conversation::find($this->selectedConversationId);
            if ($conv) {
                $conv->update(['status' => $newStatus]);
                $this->feedbackMessage = "Status erfolgreich geändert auf: {$this->formatStatusLabel($newStatus)}";
            }
        }
    }

    public function saveOfficerNotes(): void
    {
        if ($this->selectedConversationId) {
            $conv = Conversation::find($this->selectedConversationId);
            if ($conv) {
                $conv->update([
                    'officer_notes' => trim($this->officerNoteInput),
                    'assigned_officer' => trim($this->assignedOfficerInput),
                ]);
                $this->feedbackMessage = 'Sachbearbeiter-Notizen und Zuweisung erfolgreich gespeichert.';
            }
        }
    }

    public function sendOfficerMessage(): void
    {
        $text = trim($this->officerResponseInput);
        if ($text === '' || ! $this->selectedConversationId) {
            return;
        }

        $conv = Conversation::find($this->selectedConversationId);
        if ($conv) {
            $officerName = trim($this->assignedOfficerInput) ?: 'Amtliche Sachbearbeitung (Bürgerservice)';

            ConversationMessage::create([
                'conversation_id' => $conv->id,
                'uuid' => (string) Str::uuid(),
                'role' => 'assistant',
                'content' => "🏛️ **Offizielle Mitteilung der Sachbearbeitung ({$officerName}):**\n\n{$text}",
            ]);

            $conv->update([
                'status' => 'in_review',
                'assigned_officer' => $officerName,
            ]);

            $this->officerResponseInput = '';
            $this->feedbackMessage = 'Antwort wurde direkt in das Bürgergespräch übermittelt.';
        }
    }

    public function resolveCase(): void
    {
        if ($this->selectedConversationId) {
            $conv = Conversation::find($this->selectedConversationId);
            if ($conv) {
                $conv->update(['status' => 'resolved']);
                $this->feedbackMessage = 'Fall wurde erfolgreich als ERLEDIGT abgeschlossen.';
            }
        }
    }

    public function setStatusFilter(string $filter): void
    {
        $this->statusFilter = $filter;
    }

    public function formatStatusLabel(string $status): string
    {
        return match ($status) {
            'pending_officer' => 'Offen (Neu)',
            'in_review' => 'In Bearbeitung',
            'resolved' => 'Erledigt',
            default => 'Aktiv',
        };
    }

    public function render(): View
    {
        $query = Conversation::escalated()
            ->with(['messages'])
            ->latest('escalated_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (trim($this->searchQuery) !== '') {
            $search = '%'.trim($this->searchQuery).'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('escalation_reason', 'like', $search)
                    ->orWhere('assigned_officer', 'like', $search)
                    ->orWhere('id', 'like', $search);
            });
        }

        $cases = $query->get();

        $selectedConversation = $this->selectedConversationId
            ? Conversation::with('messages')->find($this->selectedConversationId)
            : null;

        $stats = [
            'total' => Conversation::escalated()->count(),
            'pending' => Conversation::escalated()->where('status', 'pending_officer')->count(),
            'in_review' => Conversation::escalated()->where('status', 'in_review')->count(),
            'resolved' => Conversation::escalated()->where('status', 'resolved')->count(),
        ];

        return view('livewire.employee-portal', [
            'cases' => $cases,
            'selectedConversation' => $selectedConversation,
            'stats' => $stats,
        ]);
    }
}
