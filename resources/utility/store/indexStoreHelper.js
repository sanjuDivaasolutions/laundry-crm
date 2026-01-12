import ApiService from "@/core/services/ApiService";

const fetchData = (route, obj) => {
    return new Promise((resolve, reject) => {
        obj.loading = true;
        ApiService.get(route, { params: obj.query })
            .then((response) => {
                obj.data = response.data.data;
                obj.total = response.data.total;
                obj.from = response.data.from;
                obj.to = response.data.to;
                resolve(response);
            })
            .catch((error) => {
                reject(error);
            })
            .finally(() => {
                obj.loading = false;
            });
    });
};
export default fetchData;
