class History {

    constructor() {
        this.init();
    }

    /**
     * Init History Service.
     */
    init() {
        this.eventStack = [];
    }

    /**
     * Add given event to event stack. / maybee better name register
     */
    add() {

    }

    /**
     * Add last undoed event from event stack.
     */
    redo() {

    }

    /**
     * Revert last event from event stack.
     */
    undo() {

    }

    /**
     * Clear service.
     */
    clear() {
        this.init();
    }
}