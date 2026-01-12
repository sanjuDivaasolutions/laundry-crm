import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";

export async function getEditData(route, id) {
    return await ApiService.get(`${route}/${id}/edit`);
}

export async function getShowData(route, id) {
    return await ApiService.get(`${route}/${id}`);
}

export async function createData(route, entry) {
    return await ApiService.post(`${route}`, entry);
}
export async function updateData(route, entry, idField = "id") {
    const id = entry[idField];
    return await ApiService.put(`${route}/${id}`, entry);
}

export async function performRemoveItem(route, id, showToast = true) {
    return new Promise((resolve, reject) => {
        ApiService.delete(`${route}/${id}`)
            .then((res) => {
                if (showToast) {
                    $toastSuccess("Successfully removed!");
                }
                resolve(res);
            })
            .catch((error) => {
                $catchResponse(error);
                reject(error);
            });
    });
}
