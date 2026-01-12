import ApiService from "./ApiService";

class UploadService {
    public static async handleUpload(endpoint: string, params: any = {}) {
        const fd = new FormData();
        fd.append("file", params.file);
        return await ApiService.upload(endpoint, fd);
    }
}

export default UploadService;
