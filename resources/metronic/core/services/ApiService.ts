import type { App } from "vue";
import axios from "axios";
import VueAxios from "vue-axios";
// @ts-ignore
import JwtService from "@/core/services/JwtService";
import type { AxiosResponse } from "axios";
// @ts-ignore
import { _ } from "lodash";

/**
 * @description service to call HTTP request via Axios
 */
class ApiService {
    /**
     * @description property to share vue instance
     */
    public static vueInstance: App;

    /**
     * @description initialize vue axios
     */
    public static init(app: App<Element>) {
        ApiService.vueInstance = app;
        ApiService.vueInstance.use(VueAxios, axios);

        // Dynamic API URL handling for multi-tenancy
        const envApiUrl = import.meta.env.VITE_APP_API_URL as string;

        try {
            // If the API URL is absolute, we want to preserve the port/proto but use current hostname
            // This ensures http://leeza.localhost:8000 calls http://leeza.localhost:8000/api/v1
            // instead of http://localhost:8000/api/v1
            if (envApiUrl.startsWith('http')) {
                const url = new URL(envApiUrl);
                // Only replace if we are not on localhost/127.0.0.1 explicitly, 
                // or if we are on a subdomain (different hostname than config)
                if (window.location.hostname !== url.hostname) {
                    url.hostname = window.location.hostname;
                }
                ApiService.vueInstance.axios.defaults.baseURL = url.toString();
            } else {
                ApiService.vueInstance.axios.defaults.baseURL = envApiUrl;
            }
        } catch (e) {
            // Fallback for relative URLs or errors
            ApiService.vueInstance.axios.defaults.baseURL = envApiUrl;
        }
    }

    /**
     * @description set the default HTTP request headers
     */
    public static setHeader(): void {
        ApiService.vueInstance.axios.defaults.headers.common[
            "Authorization"
        ] = `Bearer ${JwtService.getToken()}`;
        ApiService.vueInstance.axios.defaults.headers.common["Accept"] =
            "application/json";

        // Tenant Header
        // Prefer window object (injected by blades) or Env, fallback to default
        const tenantId = (window as any).TENANT_ID || "1";
        if (tenantId) {
            ApiService.vueInstance.axios.defaults.headers.common["X-Tenant-ID"] = tenantId;
        }
    }

    /**
     * @description send the GET HTTP request
     * @param resource: string
     * @param params: AxiosRequestConfig
     * @returns Promise<AxiosResponse>
     */
    public static query(resource: string, params: any): Promise<AxiosResponse> {
        return ApiService.vueInstance.axios.get(resource, params);
    }

    /**
     * @description send the GET HTTP request
     * @param resource: string
     * @param slug: string
     * @param params
     * @returns Promise<AxiosResponse>
     */
    public static get(
        resource: string,
        params: any = {},
        slug = "" as string
    ): Promise<AxiosResponse> {
        return ApiService.vueInstance.axios.get(`${resource}/${slug}`, params);
    }

    /**
     * @description set the POST HTTP request
     * @param resource: string
     * @param params: AxiosRequestConfig
     * @returns Promise<AxiosResponse>
     */
    public static post(
        resource: string,
        params: any,
        stringify: boolean = true
    ): Promise<AxiosResponse> {
        if (stringify) {
            params = ApiService.stringifyArrays(params);
        }
        return ApiService.vueInstance.axios.post(`${resource}`, params);
    }

    /**
     * @description set the POST HTTP request
     * @param resource: string
     * @param params: AxiosRequestConfig
     * @returns Promise<AxiosResponse>
     */
    public static upload(
        resource: string,
        params: FormData
    ): Promise<AxiosResponse> {
        return ApiService.vueInstance.axios.post(`${resource}`, params, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });
    }

    private static stringifyArrays(obj) {
        const entry = _.cloneDeep(obj);
        if (typeof entry === "object" && entry !== null) {
            Object.keys(entry).forEach((key) => {
                if (Array.isArray(entry[key])) {
                    entry[key] = JSON.stringify(entry[key]);
                }
            });
        }
        return entry;
    }

    /**
     * @description send the UPDATE HTTP request
     * @param resource: string
     * @param slug: string
     * @param params: AxiosRequestConfig
     * @returns Promise<AxiosResponse>
     */
    public static update(
        resource: string,
        slug: string,
        params: any
    ): Promise<AxiosResponse> {
        return ApiService.vueInstance.axios.put(
            `${resource}/${slug}`,
            ApiService.stringifyArrays(params)
        );
    }

    /**
     * @description Send the PUT HTTP request
     * @param resource: string
     * @param params: AxiosRequestConfig
     * @returns Promise<AxiosResponse>
     */
    public static put(resource: string, params: any): Promise<AxiosResponse> {
        return ApiService.vueInstance.axios.put(
            `${resource}`,
            ApiService.stringifyArrays(params)
        );
    }

    /**
     * @description Send the DELETE HTTP request
     * @param resource: string
     * @returns Promise<AxiosResponse>
     */
    public static delete(resource: string): Promise<AxiosResponse> {
        return ApiService.vueInstance.axios.delete(resource);
    }
}

export default ApiService;
