<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processador de Áudio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .drop-zone {
            border: 2px dashed #007bff;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            color: #6c757d;
            transition: background-color 0.2s;
        }

        .drop-zone:hover {
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Processador de Áudio</h1>
        <div class="row my-4">
            <div class="col-md-12">
                <div id="drop-zone" class="drop-zone">
                    <p>Arraste seu arquivo aqui ou clique para selecionar</p>
                    <input type="file" id="audio-file" class="d-none" accept="audio/*">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-center">
                <button id="start-processing" class="btn btn-primary" disabled>Iniciar Processamento</button>
            </div>
        </div>

        <div id="loading" class="mt-4 d-none text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2">Processando...</p>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <h3>Tabela de Arquivos Picotados</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Parte</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody id="file-table">
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h3>Transcrição</h3>
                <textarea id="transcription" class="form-control" rows="20" readonly></textarea>
                <button id="save-transcription" class="btn btn-success mt-3">Salvar Transcrição</button>
                <button id="generate-ata-docs" class="btn btn-secondary mt-3">Gerar Ata (DOCX)</button>
                <button id="generate-ata-pdf" class="btn btn-secondary mt-3">Gerar Ata (PDF)</button>
            </div>
        </div>
    </div>

    <!-- Modal para Capturar o Nome -->
    <div class="modal fade" id="nameModal" tabindex="-1" aria-labelledby="nameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nameModalLabel">Informe seu Nome</h5>
                </div>
                <div class="modal-body">
                    <form id="nameForm">
                        <div class="mb-3">
                            <label for="nameInput" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nameInput" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveName">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById("drop-zone");
        const fileInput = document.getElementById("audio-file");
        const startProcessing = document.getElementById("start-processing");
        const loading = document.getElementById("loading");
        const fileTable = document.getElementById("file-table");
        const transcription = document.getElementById("transcription");
        const saveTranscriptionButton = document.getElementById("save-transcription");
        const generateAtaDocsButton = document.getElementById("generate-ata-docs");
        const generateAtaPdfButton = document.getElementById("generate-ata-pdf");

        let uploadedFile = null;
        let userName = '';
        let fullTranscription = '';

        // Exibir o modal ao carregar a página
        const nameModal = new bootstrap.Modal(document.getElementById('nameModal'), { backdrop: 'static', keyboard: false });
        window.addEventListener('load', () => {
            nameModal.show();
        });

        // Salvar o nome e fechar a modal
        document.getElementById('saveName').addEventListener('click', () => {
            const nameInput = document.getElementById('nameInput');
            if (nameInput.value.trim() !== '') {
                userName = nameInput.value.trim();
                nameModal.hide(); // Fecha a modal
            } else {
                alert("Por favor, insira seu nome.");
            }
        });

        dropZone.addEventListener("click", () => fileInput.click());
        dropZone.addEventListener("dragover", (e) => {
            e.preventDefault();
            dropZone.classList.add("bg-light");
        });
        dropZone.addEventListener("dragleave", () => {
            dropZone.classList.remove("bg-light");
        });
        dropZone.addEventListener("drop", (e) => {
            e.preventDefault();
            dropZone.classList.remove("bg-light");
            const files = e.dataTransfer.files;
            if (files.length) {
                uploadedFile = files[0];
                dropZone.textContent = `Arquivo Selecionado: ${uploadedFile.name}`;
                startProcessing.disabled = false;
            }
        });

        fileInput.addEventListener("change", (e) => {
            const files = e.target.files;
            if (files.length) {
                uploadedFile = files[0];
                dropZone.textContent = `Arquivo Selecionado: ${uploadedFile.name}`;
                startProcessing.disabled = false;
            }
        });

        startProcessing.addEventListener("click", async () => {
            if (!uploadedFile) {
                alert("Por favor, selecione um arquivo para transcrever.");
                return;
            }

            const formData = new FormData();
            formData.append("file", uploadedFile);

            // Processar o arquivo
            loading.classList.remove("d-none");
            const response = await fetch("picotar.php", {
                method: "POST",
                body: formData,
            });

            const partsData = await response.json();
            if (!response.ok || partsData.status !== "success") {
                alert("Erro ao picotar o arquivo: " + partsData.message);
                loading.classList.add("d-none");
                return;
            }

            const parts = partsData.parts;
            let currentPart = 0;

            for (const part of parts) {
                try {
                    const transResponse = await fetch("transcrever.php", {
                        method: "POST",
                        body: new URLSearchParams({ part }),
                    });

                    const transData = await transResponse.json();

                    if (transData.status === "success") {
                        fullTranscription += ` ${transData.text}`;
                        const row = `<tr>
                            <td>${part}</td>
                            <td class="text-success">Sucesso</td>
                            <td>${new Date().toLocaleString()}</td>
                        </tr>`;
                        fileTable.innerHTML += row;
                    } else {
                        throw new Error(transData.message || "Erro na transcrição.");
                    }

                    currentPart++;
                } catch (error) {
                    console.error(`Erro ao processar ${part}:`, error);
                }
            }

            transcription.value = fullTranscription.trim();
            loading.classList.add("d-none");
            alert("Transcrição concluída com sucesso!");
        });

        saveTranscriptionButton.addEventListener("click", async () => {
            if (!uploadedFile || !fullTranscription.trim()) {
                alert("Nenhum arquivo ou transcrição disponível para salvar.");
                return;
            }

            const response = await fetch("save_audio.php", {
                method: "POST",
                body: new URLSearchParams({
                    arquivo: uploadedFile.name,
                    transcricao: fullTranscription.trim(),
                    nome: userName // Envia o nome do usuário
                }),
            });

            const result = await response.json();
            if (result.success) {
                alert("Transcrição salva com sucesso!");
            } else {
                alert("Erro ao salvar a transcrição: " + result.message);
            }
        });

        generateAtaDocsButton.addEventListener("click", async () => {
            if (!fullTranscription.trim()) {
                alert("Nenhuma transcrição disponível para gerar a ata.");
                return;
            }

            const response = await fetch("generate_ata_docs.php", {
                method: "POST",
                body: new URLSearchParams({
                    transcricao: fullTranscription.trim()
                }),
            });

            const result = await response.json();
            if (result.success) {
                const a = document.createElement("a");
                a.href = result.fileUrl; // URL do arquivo gerado
                a.download = result.fileName; // Nome do arquivo para download
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                alert("Erro ao gerar a ata (DOCX): " + result.message);
            }
        });

        generateAtaPdfButton.addEventListener("click", async () => {
            if (!fullTranscription.trim()) {
                alert("Nenhuma transcrição disponível para gerar a ata.");
                return;
            }

            const response = await fetch("generate_ata_pdf.php", {
                method: "POST",
                body: new URLSearchParams({
                    transcricao: fullTranscription.trim()
                }),
            });

            const result = await response.json();
            if (result.success) {
                const a = document.createElement("a");
                a.href = result.fileUrl; // URL do arquivo gerado
                a.download = result.fileName; // Nome do arquivo para download
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                alert("Erro ao gerar a ata (PDF): " + result.message);
            }
        });
    </script>
</body>

</html>
