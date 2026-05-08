(function () {
    const notice_container = document.getElementById("toastWrap");
    const flash_notice = document.querySelector("[data-flash-notice]");

    if (flash_notice && notice_container) {
        setTimeout(() => {
            flash_notice.remove();
        }, 2500);
    }

    // Open popup form panel.
    document.querySelectorAll("[data-open-modal], [data-open-panel]").forEach((button) => {
        button.addEventListener("click", () => {
            const target_id = button.getAttribute("data-open-modal") || button.getAttribute("data-open-panel");
            const popup_panel = document.getElementById(target_id);
            if (popup_panel) popup_panel.classList.add("open");
        });
    });

    document.querySelectorAll("[data-close-modal], [data-close-panel]").forEach((button) => {
        button.addEventListener("click", () => {
            const popup_panel = button.closest(".modal, .popup-panel");
            if (popup_panel) popup_panel.classList.remove("open");
        });
    });

    document.querySelectorAll(".modal, .popup-panel").forEach((popup_panel) => {
        popup_panel.addEventListener("click", (event) => {
            if (event.target === popup_panel) {
                popup_panel.classList.remove("open");
            }
        });
    });

    // Styled custom validation to avoid browser default popup style
    document.querySelectorAll("form[data-custom-validate='true']").forEach((form) => {
        form.setAttribute("novalidate", "novalidate");
        form.addEventListener("submit", (event) => {
            let isValid = true;
            form.querySelectorAll("[data-required='true']").forEach((field) => {
                const value = (field.value || "").trim();
                const errorBlock = form.querySelector(`[data-error-for='${field.name}']`);
                if (!value) {
                    field.classList.add("is-invalid");
                    if (errorBlock) errorBlock.textContent = "This field is required.";
                    isValid = false;
                    return;
                }

                // validate email
                if (field.type === "email") {
                    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                    if (!emailOk) {
                        field.classList.add("is-invalid");
                        if (errorBlock) errorBlock.textContent = "Please enter a valid email.";
                        isValid = false;
                        return;
                    }
                }

                field.classList.remove("is-invalid");
                if (errorBlock) errorBlock.textContent = "";
            });

            if (!isValid) event.preventDefault();
        });
    });

    // Add reusable clear (X) button for all search forms
    document.querySelectorAll("form.search-form").forEach((form) => {
        const searchInput = form.querySelector("input[name='search']");
        if (!searchInput) return;

        const inputWrapper = document.createElement("div");
        inputWrapper.className = "search-input-wrap";
        searchInput.parentNode.insertBefore(inputWrapper, searchInput);
        inputWrapper.appendChild(searchInput);

        const clearButton = document.createElement("button");
        clearButton.type = "button";
        clearButton.className = "search-clear-btn";
        clearButton.setAttribute("aria-label", "Clear search");
        clearButton.textContent = "×";
        inputWrapper.appendChild(clearButton);

        const toggleClearButton = () => {
            clearButton.style.visibility = searchInput.value.trim() ? "visible" : "hidden";
        };

        clearButton.addEventListener("click", () => {
            searchInput.value = "";
            form.submit();
        });

        searchInput.addEventListener("input", toggleClearButton);
        toggleClearButton();
    });

    // Double confirm dialog for delete links
    const confirm_delete_panel = document.getElementById("confirmDeleteModal");
    const confirm_delete_button = document.getElementById("confirmDeleteGo");
    let delete_target = "";

    document.querySelectorAll("[data-confirm-delete='true']").forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();
            delete_target = link.getAttribute("href");
            if (confirm_delete_panel) confirm_delete_panel.classList.add("open");
        });
    });

    if (confirm_delete_button) {
        confirm_delete_button.addEventListener("click", () => {
            if (delete_target) {
                window.location.href = delete_target;
            }
        });
    }
})();
