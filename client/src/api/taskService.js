import apiClient from "./axiosClient";

const route = "/tasks";

export default {
  async getAll() {
    return await apiClient.get(route);
  },
  async getById(id) {
    return await apiClient.get(`${route}/${id}`);
  },
};
