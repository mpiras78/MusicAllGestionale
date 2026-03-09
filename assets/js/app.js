/**
 * MusicAll - Main JavaScript
 */

(function($) {
    'use strict';

    // Inizializzazione al caricamento del DOM
    $(document).ready(function() {
        
        // Auto-dismiss solo flash messages (success/error temporanei)
        // Gli alert nella pagina non spariscono automaticamente
        setTimeout(function() {
            $('.alert.flash-message').fadeOut('slow');
        }, 5000);

        // Conferma eliminazione
        $('.delete-confirm').on('click', function(e) {
            if (!confirm('Sei sicuro di voler eliminare questo elemento?')) {
                e.preventDefault();
                return false;
            }
        });

        // Tooltip Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Popover Bootstrap
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Validazione form
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });

        // Cerca in tabella
        $('#searchTable').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#dataTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Select All checkbox
        $('#selectAll').on('change', function() {
            $('.select-item').prop('checked', $(this).prop('checked'));
        });

        // Print page
        $('.btn-print').on('click', function() {
            window.print();
        });

        // Export to CSV
        $('.btn-export-csv').on('click', function() {
            exportTableToCSV('export.csv');
        });
    });

    // Funzione per esportare tabella in CSV
    function exportTableToCSV(filename) {
        var csv = [];
        var rows = document.querySelectorAll('#dataTable tr');
        
        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (var j = 0; j < cols.length; j++) {
                row.push(cols[j].innerText);
            }
            
            csv.push(row.join(','));
        }
        
        downloadCSV(csv.join('\n'), filename);
    }

    function downloadCSV(csv, filename) {
        var csvFile;
        var downloadLink;
        
        csvFile = new Blob([csv], {type: 'text/csv'});
        downloadLink = document.createElement('a');
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }

    // Loading overlay
    window.showLoading = function() {
        $('body').append('<div class="spinner-overlay"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    };

    window.hideLoading = function() {
        $('.spinner-overlay').remove();
    };

    // Toast notification
    window.showToast = function(message, type = 'info') {
        var bgClass = 'bg-' + type;
        var toastId = 'toast-' + Date.now();
        var toast = `
            <div id="${toastId}" class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                <div class="toast show ${bgClass} text-white" role="alert">
                    <div class="toast-header ${bgClass} text-white">
                        <strong class="me-auto">Notifica</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" onclick="$('#${toastId}').remove()"></button>
                    </div>
                    <div class="toast-body" style="cursor: pointer;" onclick="$('#${toastId}').fadeOut(function() { $(this).remove(); })">
                        ${message}
                    </div>
                </div>
            </div>
        `;
        $('body').append(toast);
        // Toast non sparisce automaticamente - si chiude cliccandoci sopra o sulla X
    };

    // AJAX form submit
    window.submitAjaxForm = function(formId, successCallback) {
        $(formId).on('submit', function(e) {
            e.preventDefault();
            
            showLoading();
            
            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        showToast(response.message || 'Operazione completata con successo', 'success');
                        if (successCallback) {
                            successCallback(response);
                        }
                    } else {
                        showToast(response.message || 'Si è verificato un errore', 'danger');
                    }
                },
                error: function() {
                    hideLoading();
                    showToast('Errore di connessione al server', 'danger');
                }
            });
        });
    };

})(jQuery);

// Calendario functions
var CalendarioApp = {
    
    init: function() {
        this.bindEvents();
        this.loadCalendar();
    },
    
    bindEvents: function() {
        // Cambio giorno
        $(document).on('change', '#selectGiorno', function() {
            CalendarioApp.loadCalendar();
        });
        
        // Click su slot lezione
        $(document).on('click', '.lezione-slot', function() {
            var lezioneId = $(this).data('lezione-id');
            CalendarioApp.showLezioneDetail(lezioneId);
        });
        
        // Click su cella vuota per aggiungere lezione
        $(document).on('click', '.calendario-cell:empty', function() {
            var aula = $(this).data('aula-id');
            var ora = $(this).data('ora');
            var giorno = $('#selectGiorno').val();
            CalendarioApp.showAddLezione(giorno, aula, ora);
        });
    },
    
    loadCalendar: function() {
        var giorno = $('#selectGiorno').val() || 'lunedi';
        
        showLoading();
        
        $.ajax({
            url: 'api/calendario.php',
            type: 'GET',
            data: { giorno: giorno },
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    CalendarioApp.renderCalendar(response.data);
                }
            },
            error: function() {
                hideLoading();
                showToast('Errore nel caricamento del calendario', 'danger');
            }
        });
    },
    
    renderCalendar: function(data) {
        // Implementazione rendering calendario
        // Da personalizzare in base ai dati
    },
    
    showLezioneDetail: function(lezioneId) {
        // Mostra dettagli lezione in modal
        $.ajax({
            url: 'api/lezioni.php',
            type: 'GET',
            data: { id: lezioneId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var modal = $('#lezioneDetailModal');
                    // Popola modal con i dati
                    modal.find('.modal-body').html(CalendarioApp.renderLezioneDetail(response.data));
                    modal.modal('show');
                }
            }
        });
    },
    
    renderLezioneDetail: function(lezione) {
        return `
            <div class="mb-3">
                <strong>Socio:</strong> ${lezione.socio_nome}
            </div>
            <div class="mb-3">
                <strong>Docente:</strong> ${lezione.docente_nome}
            </div>
            <div class="mb-3">
                <strong>Materia:</strong> ${lezione.materia}
            </div>
            <div class="mb-3">
                <strong>Orario:</strong> ${lezione.ora_inizio} - ${lezione.ora_fine}
            </div>
        `;
    },
    
    showAddLezione: function(giorno, aula, ora) {
        // Mostra form per aggiungere lezione
        $('#addLezioneModal').modal('show');
        $('#inputGiorno').val(giorno);
        $('#inputAula').val(aula);
        $('#inputOra').val(ora);
    }
};