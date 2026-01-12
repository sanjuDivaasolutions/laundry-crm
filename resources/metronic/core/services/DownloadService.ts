import { AxiosResponse } from "axios";
import ApiService from "./ApiService";

class DownloadService {
    public static async handleDownload(endpoint: string, params: any = {}) {
        params.responseType = "blob";
        this.downloadFile(await ApiService.get(endpoint, params));
    }

    public static async handleDownloadAndPrint(
        endpoint: string,
        params: any = {}
    ) {
        params.responseType = "blob";
        this.openPrintDialog(await ApiService.get(endpoint, params));
    }

    public static downloadFile(res) {
        const contentDisposition = res.headers["content-disposition"];
        let fileName = "filename.pdf";
        if (contentDisposition) {
            const matches = contentDisposition.match(
                /filename[^;=\n]*=(([\"']).*?\2|[^;\n]*)/i
            );
            if (matches && matches[1]) {
                fileName = matches[1].replace(/["']/g, "");
            }
        }
        const contentType = res.headers["Content-Type"];

        const fileURL = window.URL.createObjectURL(
            new Blob([res.data], { type: contentType })
        );
        const fileLink = document.createElement("a");
        fileLink.href = fileURL;
        fileLink.setAttribute("download", fileName);
        document.body.appendChild(fileLink);
        fileLink.click();
    }

    public static openPrintDialog(res: AxiosResponse<any, any>) {
        const pdfBlob = res.data;

        // Create a Blob URL for the PDF
        const pdfUrl = URL.createObjectURL(pdfBlob);

        // Open the print dialog
        const printWindow = window.open(pdfUrl, "_blank");
        if (printWindow) {
            printWindow.onload = () => {
                // After the window has loaded, trigger the print dialog
                printWindow.print();
            };
        } else {
            console.error("Error opening print window");
        }

        // Important: Release the Blob URL after use to free up resources
        URL.revokeObjectURL(pdfUrl);
    }
}

export default DownloadService;
