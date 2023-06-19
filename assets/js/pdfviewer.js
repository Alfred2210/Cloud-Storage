import * as pdfjsLib from 'pdfjs-dist/build/pdf';

const pdfUrl = '/path/to/your/pdf/file.pdf';

pdfjsLib.GlobalWorkerOptions.workerSrc = 'pdfjs-dist/build/pdf.worker.js';

const loadingTask = pdfjsLib.getDocument(pdfUrl);
loadingTask.promise.then(function(pdf) {
    const pageNumber = 1;
    pdf.getPage(pageNumber).then(function(page) {
        const scale = 1.5;
        const viewport = page.getViewport({scale: scale});

        const canvas = document.getElementById('pdfCanvas');
        const context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: context,
            viewport: viewport
        };
        page.render(renderContext);
    });
});
