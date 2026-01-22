export default function timer(config) {
    return {
        todoId: config.todoId,
        isRunning: config.isRunning || false,
        startedAt: config.startedAt ? new Date(config.startedAt) : null,
        totalSeconds: Math.max(0, config.totalSeconds || 0),
        currentSessionSeconds: 0,
        displayTime: '00:00',
        intervalId: null,
        isLoading: false,

        init() {
            this.updateDisplay();
            if (this.isRunning && this.startedAt) {
                this.calculateCurrentSession();
                this.startInterval();
            }
        },

        calculateCurrentSession() {
            if (this.startedAt) {
                const now = new Date();
                this.currentSessionSeconds = Math.max(0, Math.floor((now - this.startedAt) / 1000));
            }
        },

        startInterval() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
            }
            this.intervalId = setInterval(() => {
                this.currentSessionSeconds++;
                this.updateDisplay();
            }, 1000);
        },

        stopInterval() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        },

        updateDisplay() {
            const total = this.totalSeconds + (this.isRunning ? this.currentSessionSeconds : 0);
            this.displayTime = this.formatTime(total);
        },

        formatTime(seconds) {
            // Ensure seconds is never negative
            seconds = Math.max(0, seconds);

            if (seconds < 3600) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }
            const hours = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        },

        async toggleTimer() {
            if (this.isLoading) return;

            if (this.isRunning) {
                await this.stopTimer();
            } else {
                await this.startTimer();
            }
        },

        async startTimer() {
            this.isLoading = true;
            try {
                const response = await fetch(`/tarefas/${this.todoId}/time/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    const error = await response.json();
                    console.error('Error starting timer:', error.message);
                    return;
                }

                const data = await response.json();
                this.isRunning = true;
                this.startedAt = new Date(data.todo.timer_started_at);
                this.currentSessionSeconds = 0;
                this.startInterval();
            } catch (error) {
                console.error('Error starting timer:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async stopTimer() {
            this.isLoading = true;
            try {
                const response = await fetch(`/tarefas/${this.todoId}/time/stop`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    const error = await response.json();
                    console.error('Error stopping timer:', error.message);
                    return;
                }

                const data = await response.json();
                this.stopInterval();
                this.isRunning = false;
                this.startedAt = null;
                this.totalSeconds = data.todo.total_time_seconds;
                this.currentSessionSeconds = 0;
                this.updateDisplay();

                // Dispatch event for edit page to update history
                this.$dispatch('timer-stopped', { todoId: this.todoId, timeEntry: data.time_entry });
            } catch (error) {
                console.error('Error stopping timer:', error);
            } finally {
                this.isLoading = false;
            }
        },

        destroy() {
            this.stopInterval();
        }
    };
}
