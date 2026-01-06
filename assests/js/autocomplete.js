/**
 * Search Autocomplete - YouTube Style
 */

class SearchAutocomplete {
    constructor(inputElement) {
        this.input = inputElement;
        this.form = inputElement.closest('.search-form');
        this.suggestionsContainer = null;
        this.selectedIndex = -1;
        this.suggestions = [];
        this.timeout = null;
        
        this.init();
    }
    
    init() {
        this.createSuggestionsContainer();
        
        this.input.addEventListener('input', (e) => this.handleInput(e));
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        document.addEventListener('click', (e) => {
            if (!this.form.contains(e.target)) {
                this.hideSuggestions();
            }
        });
    }
    
    createSuggestionsContainer() {
        this.suggestionsContainer = document.createElement('div');
        this.suggestionsContainer.className = 'search-suggestions';
        this.form.appendChild(this.suggestionsContainer);
    }
    
    handleInput(e) {
        const query = e.target.value.trim();
        
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        
        if (query.length < 1) {
            this.hideSuggestions();
            return;
        }
        
        this.timeout = setTimeout(() => {
            this.fetchSuggestions(query);
        }, 300);
    }
    
    async fetchSuggestions(query) {
        try {
            const response = await fetch(`get_search_suggestions.php?q=${encodeURIComponent(query)}`);
            const suggestions = await response.json();
            
            this.suggestions = suggestions;
            this.selectedIndex = -1;
            
            this.renderSuggestions(suggestions);
        } catch (error) {
            console.error('Autocomplete error:', error);
            this.hideSuggestions();
        }
    }
    
    renderSuggestions(suggestions) {
        if (!suggestions || suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }
        
        const html = suggestions.map((name, index) => {
            return `
                <div class="suggestion-item" data-index="${index}" data-name="${this.escapeHtml(name)}">
                    <ion-icon name="search-outline" class="suggestion-icon"></ion-icon>
                    <div class="suggestion-text">${this.escapeHtml(name)}</div>
                </div>
            `;
        }).join('');
        
        this.suggestionsContainer.innerHTML = html;
        this.suggestionsContainer.classList.add('active');
        
        this.suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectSuggestion(item.dataset.name);
            });
        });
    }
    
    handleKeydown(e) {
        if (!this.suggestionsContainer.classList.contains('active')) {
            return;
        }
        
        const items = this.suggestionsContainer.querySelectorAll('.suggestion-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                this.updateSelection(items);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection(items);
                break;
                
            case 'Enter':
                if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                    e.preventDefault();
                    this.selectSuggestion(items[this.selectedIndex].dataset.name);
                }
                break;
                
            case 'Escape':
                this.hideSuggestions();
                break;
        }
    }
    
    updateSelection(items) {
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === this.selectedIndex);
        });
        
        if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
            items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }
    
    selectSuggestion(name) {
        this.input.value = name;
        this.hideSuggestions();
        this.form.submit();
    }
    
    showSuggestions() {
        this.suggestionsContainer.classList.add('active');
    }
    
    hideSuggestions() {
        this.suggestionsContainer.classList.remove('active');
        this.selectedIndex = -1;
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const searchInputs = document.querySelectorAll('.search-form input[name="q"]');
    searchInputs.forEach(input => {
        new SearchAutocomplete(input);
    });
});
