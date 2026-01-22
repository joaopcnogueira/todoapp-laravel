export default function timeEntries(config) {
    return {
        todoId: config.todoId,
        entries: config.entries || [],

        async deleteEntry(entryId) {
            if (!confirm('Excluir esta entrada de tempo?')) return;
            try {
                const response = await fetch(`/tarefas/${this.todoId}/time/${entryId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    credentials: 'same-origin',
                });
                if (response.ok) {
                    this.entries = this.entries.filter(e => e.id !== entryId);
                }
            } catch (error) {
                console.error('Error deleting entry:', error);
            }
        },

        handleTimerStopped(event) {
            if (event.detail.todoId === this.todoId) {
                const timeEntry = event.detail.timeEntry;
                this.entries.unshift({
                    id: timeEntry.id,
                    started_at: new Date(timeEntry.started_at).toLocaleString('pt-BR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    }),
                    ended_at: new Date(timeEntry.ended_at).toLocaleString('pt-BR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    }),
                    formatted_duration: timeEntry.formatted_duration,
                    is_running: false,
                });
            }
        }
    };
}
