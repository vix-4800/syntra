class SyntraWebApp {
    constructor() {
        this.commands = [];
        this.currentCommand = null;
        this.isExecuting = false;

        this.init();
    }

    init() {
        this.bindEvents();
        this.loadCommands();
    }

    bindEvents() {
        // Refresh commands button
        document
            .getElementById("refresh-commands")
            .addEventListener("click", () => {
                this.loadCommands();
            });

        // Execute command button
        document
            .getElementById("execute-command")
            .addEventListener("click", () => {
                this.executeCommand();
            });

        // Tab switching
        document.querySelectorAll(".tab-button").forEach((button) => {
            button.addEventListener("click", (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });
    }

    async loadCommands() {
        const loadingElement = document.getElementById("commands-loading");
        const groupsContainer = document.getElementById("command-groups");

        try {
            loadingElement.style.display = "block";

            const response = await fetch("/api/commands");
            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`
                );
            }

            this.commands = await response.json();
            this.renderCommands();
        } catch (error) {
            console.error("Failed to load commands:", error);
            groupsContainer.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load commands: ${error.message}</p>
                    <button onclick="app.loadCommands()" class="retry-button">Retry</button>
                </div>
            `;
        } finally {
            loadingElement.style.display = "none";
        }
    }

    renderCommands() {
        const groupsContainer = document.getElementById("command-groups");

        // Group commands by their group
        const groupedCommands = {};
        this.commands.forEach((command) => {
            if (!groupedCommands[command.group]) {
                groupedCommands[command.group] = [];
            }
            groupedCommands[command.group].push(command);
        });

        // Generate HTML for each group
        const html = Object.entries(groupedCommands)
            .map(([group, commands]) => {
                const commandItems = commands
                    .map(
                        (command) => `
                <div class="command-item" data-command="${command.class}">
                    <h4>${command.name}</h4>
                    <p>${command.description}</p>
                </div>
            `
                    )
                    .join("");

                return `
                <div class="command-group">
                    <div class="group-header" data-group="${group}">
                        <span>${this.formatGroupName(group)}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="command-list" data-group="${group}">
                        ${commandItems}
                    </div>
                </div>
            `;
            })
            .join("");

        groupsContainer.innerHTML = html;

        // Bind events for command selection and group toggling
        this.bindCommandEvents();
    }

    bindCommandEvents() {
        // Group header toggling
        document.querySelectorAll(".group-header").forEach((header) => {
            header.addEventListener("click", (e) => {
                const group = e.currentTarget.dataset.group;
                const commandList = document.querySelector(
                    `.command-list[data-group="${group}"]`
                );
                const isCollapsed = commandList.classList.contains("collapsed");

                if (isCollapsed) {
                    commandList.classList.remove("collapsed");
                    e.currentTarget.classList.remove("collapsed");
                } else {
                    commandList.classList.add("collapsed");
                    e.currentTarget.classList.add("collapsed");
                }
            });
        });

        // Command selection
        document.querySelectorAll(".command-item").forEach((item) => {
            item.addEventListener("click", (e) => {
                const commandClass = e.currentTarget.dataset.command;
                this.selectCommand(commandClass);
            });
        });
    }

    selectCommand(commandClass) {
        // Find the command
        const command = this.commands.find((cmd) => cmd.class === commandClass);
        if (!command) return;

        // Update UI
        document.querySelectorAll(".command-item").forEach((item) => {
            item.classList.remove("active");
        });
        document
            .querySelector(`[data-command="${commandClass}"]`)
            .classList.add("active");

        // Show command panel
        document.getElementById("welcome-screen").style.display = "none";
        document.getElementById("command-panel").style.display = "block";

        // Update command info
        document.getElementById("command-name").textContent = command.name;
        document.getElementById("command-description").textContent =
            command.description;
        document.getElementById("command-group").textContent =
            this.formatGroupName(command.group);

        // Reset execution area
        document.getElementById("execution-area").style.display = "none";

        this.currentCommand = command;
    }

    async executeCommand() {
        if (!this.currentCommand || this.isExecuting) return;

        const executeButton = document.getElementById("execute-command");
        const executionArea = document.getElementById("execution-area");
        const executionStatus = document.getElementById("execution-status");
        const executionTime = document.getElementById("execution-time");

        try {
            this.isExecuting = true;
            executeButton.classList.add("loading");
            executeButton.disabled = true;

            // Show execution area
            executionArea.style.display = "block";
            executionStatus.className = "status running";
            executionStatus.textContent = "Running...";
            executionTime.textContent = "";

            // Prepare options
            const options = {};
            const projectPath = document
                .getElementById("project-path")
                .value.trim();
            const dryRun = document.getElementById("dry-run").checked;

            if (projectPath) {
                options.path = projectPath;
            }
            if (dryRun) {
                options["dry-run"] = true;
            }

            // Execute command
            const startTime = Date.now();
            const response = await fetch("/api/execute", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    command: this.currentCommand.class,
                    options: options,
                }),
            });

            const result = await response.json();
            const endTime = Date.now();
            const clientExecutionTime = endTime - startTime;

            if (response.ok) {
                // Update status
                executionStatus.className = `status ${
                    result.success ? "success" : "error"
                }`;
                executionStatus.textContent = result.success
                    ? "Success"
                    : "Failed";
                executionTime.textContent = `${result.execution_time}ms (server) / ${clientExecutionTime}ms (total)`;

                // Display output
                this.displayOutput(result);
            } else {
                throw new Error(result.error || "Unknown error");
            }
        } catch (error) {
            console.error("Command execution failed:", error);
            executionStatus.className = "status error";
            executionStatus.textContent = "Error";
            executionTime.textContent = "";

            // Show error in output
            this.displayError(error.message);
        } finally {
            this.isExecuting = false;
            executeButton.classList.remove("loading");
            executeButton.disabled = false;
        }
    }

    displayOutput(result) {
        const formattedContent = document.getElementById("formatted-content");
        const rawContent = document.getElementById("raw-content");

        // Set raw output
        rawContent.textContent = result.raw_output || "No output";

        // Create formatted output
        const formatted = this.formatOutput(result.output);
        formattedContent.innerHTML = formatted;

        // Switch to formatted tab by default
        this.switchTab("formatted");
    }

    displayError(message) {
        const formattedContent = document.getElementById("formatted-content");
        const rawContent = document.getElementById("raw-content");

        const errorHtml = `
            <div class="output-section">
                <h4><i class="fas fa-exclamation-triangle"></i> Error</h4>
                <ul class="output-list error">
                    <li>${this.escapeHtml(message)}</li>
                </ul>
            </div>
        `;

        formattedContent.innerHTML = errorHtml;
        rawContent.textContent = message;
    }

    formatOutput(output) {
        let html = "";

        // Success messages
        if (output.success && output.success.length > 0) {
            html += `
                <div class="output-section">
                    <h4><i class="fas fa-check-circle"></i> Success</h4>
                    <ul class="output-list success">
                        ${output.success
                            .map((msg) => `<li>${this.escapeHtml(msg)}</li>`)
                            .join("")}
                    </ul>
                </div>
            `;
        }

        // Error messages
        if (output.errors && output.errors.length > 0) {
            html += `
                <div class="output-section">
                    <h4><i class="fas fa-exclamation-triangle"></i> Errors</h4>
                    <ul class="output-list error">
                        ${output.errors
                            .map((msg) => `<li>${this.escapeHtml(msg)}</li>`)
                            .join("")}
                    </ul>
                </div>
            `;
        }

        // Warning messages
        if (output.warnings && output.warnings.length > 0) {
            html += `
                <div class="output-section">
                    <h4><i class="fas fa-exclamation-circle"></i> Warnings</h4>
                    <ul class="output-list warning">
                        ${output.warnings
                            .map((msg) => `<li>${this.escapeHtml(msg)}</li>`)
                            .join("")}
                    </ul>
                </div>
            `;
        }

        // Tables
        if (output.tables && output.tables.length > 0) {
            output.tables.forEach((table, index) => {
                html += `
                    <div class="output-section">
                        <h4><i class="fas fa-table"></i> Table ${index + 1}</h4>
                        <table class="output-table">
                            <thead>
                                <tr>
                                    ${table.headers
                                        .map(
                                            (header) =>
                                                `<th>${this.escapeHtml(
                                                    header
                                                )}</th>`
                                        )
                                        .join("")}
                                </tr>
                            </thead>
                            <tbody>
                                ${table.rows
                                    .map(
                                        (row) => `
                                    <tr>
                                        ${row
                                            .map(
                                                (cell) =>
                                                    `<td>${this.escapeHtml(
                                                        cell
                                                    )}</td>`
                                            )
                                            .join("")}
                                    </tr>
                                `
                                    )
                                    .join("")}
                            </tbody>
                        </table>
                    </div>
                `;
            });
        }

        // Info messages
        if (output.info && output.info.length > 0) {
            html += `
                <div class="output-section">
                    <h4><i class="fas fa-info-circle"></i> Information</h4>
                    <ul class="output-list info">
                        ${output.info
                            .map((msg) => `<li>${this.escapeHtml(msg)}</li>`)
                            .join("")}
                    </ul>
                </div>
            `;
        }

        return html || "<p>No formatted output available.</p>";
    }

    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll(".tab-button").forEach((button) => {
            button.classList.remove("active");
        });
        document
            .querySelector(`[data-tab="${tabName}"]`)
            .classList.add("active");

        // Update tab content
        document.querySelectorAll(".tab-content").forEach((content) => {
            content.classList.remove("active");
        });
        document.getElementById(`${tabName}-output`).classList.add("active");
    }

    formatGroupName(groupName) {
        return groupName.charAt(0).toUpperCase() + groupName.slice(1);
    }

    escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize the app when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    window.app = new SyntraWebApp();
});
