import './bootstrap';
import.meta.glob([
    '../images/**'
]);
// const toggleBtn = document.getElementById('toggleSidebar');
// const sidebar = document.getElementById('sidebar');
// const mainContent = document.getElementById('mainContent');

// let isSidebarVisible = true;

// toggleBtn.addEventListener('click', () => {
//   isSidebarVisible = !isSidebarVisible;

//   if (isSidebarVisible) {
//     sidebar.classList.remove('-translate-x-full');
//     mainContent.classList.remove('pl-0');
//     mainContent.classList.add('pl-[270px]');
//   } else {
//     sidebar.classList.add('-translate-x-full');
//     mainContent.classList.remove('pl-[270px]');
//     mainContent.classList.add('pl-0');
//   }
// });


//TAGS SECTION HANDLER GOES HERE
    document.addEventListener('DOMContentLoaded', function() {
        const tagInputField = document.getElementById('tag-input-field');
        const tagInputWrapper = document.getElementById('tag-input-wrapper');
        const hiddenTagsInput = document.getElementById('hidden-tags-input');

        let currentTags = [];

       
        function updateHiddenTagsInput() {
            hiddenTagsInput.value = currentTags.join(',');
        }

     
        function addTag(tagText) {
            const trimmedTag = tagText.trim();
            const errorMessageDiv = document.getElementById('tag-error-message');
            if (
                trimmedTag === '' ||
                currentTags.includes(trimmedTag) ||
                /\s/.test(trimmedTag) // Prevent tags with spaces
            ) {
                if (/\s/.test(trimmedTag)) {
                    showTagError("Tags cannot contain spaces.");
                    return;
                }
                if (currentTags.includes(trimmedTag)) {
            showTagError("You already added this tag.");
            return;
        }
                return;
            }

            currentTags.push(trimmedTag);
            updateHiddenTagsInput();

            const tagBadge = document.createElement('span');
            tagBadge.className = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors border-transparent ';
            tagBadge.innerHTML = `
                ${trimmedTag}
                <button type="button" class="ml-1 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-muted-foreground hover:bg-muted hover:text-foreground focus:outline-none " aria-label="Remove ${trimmedTag} tag">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            `;

          
            const removeButton = tagBadge.querySelector('button');
            removeButton.addEventListener('click', function() {
                removeTag(trimmedTag);
            });

         
            tagInputWrapper.insertBefore(tagBadge, tagInputField);
        }


        function removeTag(tagText) {
            currentTags = currentTags.filter(tag => tag !== tagText);
            updateHiddenTagsInput();

          
            const badges = tagInputWrapper.querySelectorAll('.inline-flex'); 
            badges.forEach(badge => {
                if (badge.textContent.trim().startsWith(tagText)) { // Simple check, might need refinement for complex tags
                    badge.remove();
                }
            });
        }

       
        tagInputField.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); 
                addTag(tagInputField.value);
                tagInputField.value = ''; 
            } else if (event.key === 'Backspace' && tagInputField.value === '') {
                event.preventDefault();
                if (currentTags.length > 0) {
                    const lastTag = currentTags[currentTags.length - 1];
                    removeTag(lastTag);
                }
            }
        });

      
    });

function showTagError(message) {
    const errorMessageDiv = document.getElementById('tag-error-message');
    errorMessageDiv.innerHTML = `
        <div class="tag-error-card">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414-1.414A9 9 0 105.636 18.364l1.414 1.414A9 9 0 1018.364 5.636z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" />
            </svg>
            <span>${message}</span>
        </div>
    `;
    errorMessageDiv.classList.remove('hidden');
    setTimeout(() => {
        errorMessageDiv.classList.add('hidden');
        errorMessageDiv.innerHTML = '';
    }, 2000);
}


